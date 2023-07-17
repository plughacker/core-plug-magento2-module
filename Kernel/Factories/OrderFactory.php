<?php

namespace PlugHacker\PlugCore\Kernel\Factories;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Aggregates\Order;
use PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException;
use PlugHacker\PlugCore\Kernel\Exceptions\NotFoundException;
use PlugHacker\PlugCore\Kernel\Interfaces\FactoryInterface;
use PlugHacker\PlugCore\Kernel\Interfaces\PlatformOrderInterface;
use PlugHacker\PlugCore\Kernel\Repositories\ChargeRepository;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\OrderId;
use PlugHacker\PlugCore\Kernel\ValueObjects\OrderStatus;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use PlugHacker\PlugCore\Payment\Factories\CustomerFactory;
use PlugHacker\PlugCore\Recurrence\Aggregates\Subscription;
use Throwable;

class OrderFactory implements FactoryInterface
{
    /**
     *
     * @param array $postData
     * @return \PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity|Order
     * @throws NotFoundException
     * @throws \PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException
     */
    public function createFromPostData($postData)
    {
        $order = new Order();

        $baseStatus = explode('_', $postData['status']);

        $status = $baseStatus[0];

        for ($i = 1; $i < count($baseStatus); $i++) {
            $status .= ucfirst(($baseStatus[$i]));
        }

        $order->setPlugId(new OrderId($postData['id']));

        try {
            OrderStatus::$status();
        } catch (Throwable $e) {
            throw new InvalidParamException(
                "Invalid order status!",
                $status
            );
        }

        $order->setStatus(OrderStatus::$status());

        $order->setPlatformOrder(
            $this->getPlatformOrder($postData['orderId'])
        );


        $charges = $postData['transactionRequests'];
        $chargeFactory = new ChargeFactory();
        foreach ($charges as $charge) {
            $charge['order'] = [
                'id' => $order->getPlugId()->getValue()
            ];
            $charge['status'] = $postData['status'];
            if (isset($postData['paymentMethod']) && is_array($postData['paymentMethod'])) {
                $charge = array_merge($charge, $postData['paymentMethod']);
            }
            $charge['transactionRequests'] = $charge;
            $newCharge = $chargeFactory->createFromPostData($charge);
            $order->addCharge($newCharge);
        }

        if (!empty($postData['customer'])) {
            $customerFactory = new CustomerFactory();
            $customer = $customerFactory->createFromPostData($postData['customer']);
            $order->setCustomer($customer);
        }

        return $order;
    }

    /**
     *
     * @param array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData)
    {
        $order = new Order;

        $order->setId($dbData['id']);
        $order->setPlugId(new OrderId($dbData['plug_id']));

        $status = $dbData['status'];
        try {
            OrderStatus::$status();
        } catch (Throwable $e) {
            throw new InvalidParamException(
                "Invalid order status!",
                $status
            );
        }
        $order->setStatus(OrderStatus::$status());

        $chargeRepository = new ChargeRepository();
        $charges = $chargeRepository->findByOrderId($order->getPlugId());

        foreach ($charges as $charge) {
            $order->addCharge($charge);
        }

        $order->setPlatformOrder(
            $this->getPlatformOrder($dbData['code'])
        );

        return $order;
    }

    private function getPlatformOrder($code)
    {
        $orderDecoratorClass =
            MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);

        /**
         *
         * @var PlatformOrderInterface $order
         */
        $order = new $orderDecoratorClass();
        $order->loadByIncrementId($code);
        return $order;
    }

    /**
     * @param PlatformOrderInterface $platformOrder
     * @param $orderId
     * @return Order
     * @throws InvalidParamException
     * @throws NotFoundException
     */
    public function createFromPlatformData(
        PlatformOrderInterface $platformOrder,
        $orderId
    ) {
        $order = new Order();

        $order->setPlugId(new OrderId($orderId));

        $baseStatus = explode('_', $platformOrder->getStatus());
        $status = $baseStatus[0];
        for ($i = 1; $i < count($baseStatus); $i++) {
            $status .= ucfirst(($baseStatus[$i]));
        }

        if ($platformOrder->getCode() === null) {
            throw new NotFoundException("Order not found: {$orderId}");
        }

        try {
            OrderStatus::$status();
        } catch (Throwable $e) {
            throw new InvalidParamException(
                "Invalid order status!",
                $status
            );
        }
        $order->setStatus(OrderStatus::$status());
        $order->setPlatformOrder($platformOrder);

        return $order;
    }

    public function createFromSubscriptionData(
        Subscription $subscription,
        $platformOrderStatus
    ) {
        $order = new Order();

        try {
            OrderStatus::$platformOrderStatus();
        } catch (Throwable $e) {
            throw new InvalidParamException(
                "Invalid order status!",
                $platformOrderStatus
            );
        }

        $order->setStatus(OrderStatus::$platformOrderStatus());
        $order->setPlatformOrder($subscription->getPlatformOrder());

        if ($subscription->getCurrentCharge()) {
            $order->addCharge($subscription->getCurrentCharge());
        }

        if ($subscription->getCustomer()) {
            $order->setCustomer($subscription->getCustomer());
        }

        return $order;
    }
}
