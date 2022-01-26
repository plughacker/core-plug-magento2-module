<?php

namespace PlugHacker\PlugCore\Recurrence\Factories;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException;
use PlugHacker\PlugCore\Kernel\Interfaces\FactoryInterface;
use PlugHacker\PlugCore\Kernel\Interfaces\PlatformOrderInterface;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\SubscriptionId;
use PlugHacker\PlugCore\Kernel\ValueObjects\PaymentMethod;
use PlugHacker\PlugCore\Payment\Factories\CustomerFactory;
use PlugHacker\PlugCore\Recurrence\Aggregates\Charge;
use PlugHacker\PlugCore\Recurrence\Aggregates\Plan;
use PlugHacker\PlugCore\Recurrence\Aggregates\Subscription;
use PlugHacker\PlugCore\Recurrence\Services\RecurrenceService;
use PlugHacker\PlugCore\Recurrence\ValueObjects\PlanId;
use PlugHacker\PlugCore\Recurrence\ValueObjects\SubscriptionStatus;
use PlugHacker\PlugCore\Recurrence\ValueObjects\IntervalValueObject;

class SubscriptionFactory implements FactoryInterface
{
    /**
     * @param array $postData
     * @return AbstractEntity|Subscription
     * @throws InvalidParamException
     */
    public function createFromPostData($postData)
    {
        $subscription = new Subscription();

        $subscription->setSubscriptionId(new SubscriptionId($postData['id']));
        $subscription->setPlugId(new SubscriptionId($postData['id']));
        $subscription->setStatus(SubscriptionStatus::{$postData['status']}());
        $subscription->setPaymentMethod(PaymentMethod::{$postData['payment_method']}());

        $subscription->setCode($postData['code']);
        $subscription->setInstallments($postData['installments']);
        $subscription->setIntervalType($postData['interval']);
        $subscription->setIntervalCount($postData['interval_count']);
        $subscription->setPlatformOrder($this->getPlatformOrder($postData['code']));

        $this->setCurrentCharge($postData, $subscription);
        $this->setCustomer($postData, $subscription);
        $this->setCurrentCycle($postData, $subscription);

        if (isset($postData['invoice'])) {
            $subscription->setInvoice($postData['invoice']);
        }

        if (isset($postData['plan_id'])) {
            $subscription->setPlanId(new PlanId($postData['plan_id']));
            $subscription->setRecurrenceType(Plan::RECURRENCE_TYPE);
        }

        if (!empty($postData['items'])) {
            foreach ($postData['items'] as $item) {
                $item['code'] = $this->getProductCode($item, $subscription); //@TODO Fix when Mark1 implement code
                $item['subscription_id'] = $postData['id'];
                $subscriptionItemFactory = new SubscriptionItemFactory();
                $subscriptionItem = $subscriptionItemFactory->createFromPostData($item);
                $subscription->addItem($subscriptionItem);
            }
        }

        return $subscription;
    }

    /**
     * @todo Remove when be implemented code on mark1
     */
    private function getProductCode($item, $subscription)
    {
        if (!empty($item['code'])) {
            return $item['code'];
        }

        return $this->getCode($item, $subscription);
    }

    /**
     * @todo Remove when be implemented code on mark1
     */
    private function getCode($item, $subscription)
    {
        if(empty($item['name'])) {
            return null;
        }

        $productName = $item['name'];
        $recurrenceService = new RecurrenceService();

        $subProduct = $recurrenceService->getSubProductByNameAndRecurrenceType(
            $productName,
            $subscription
        );

        if ($subProduct) {
            return $subProduct->getProductId();
        }

        $subscriptionItem = $recurrenceService->getSubscriptionItemByProductId(
            $item['id']
        );

        if ($subscriptionItem) {
            return $subscriptionItem->getCode();
        }

        return null;
    }

    private function getPlatformOrder($code)
    {
        $orderDecoratorClass =
            MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);

        /**
         * @var PlatformOrderInterface $order
         */
        $order = new $orderDecoratorClass();
        $order->loadByIncrementId($code);

        return $order;
    }

    /**
     * @param array $dbData
     * @return AbstractEntity|Subscription
     * @throws InvalidParamException
     */
    public function createFromDbData($dbData)
    {
        $subscription = new Subscription();

        $subscription->setId($dbData['id']);
        $subscription->setSubscriptionId(new SubscriptionId($dbData['plug_id']));
        $subscription->setCode($dbData['code']);
        $subscription->setStatus(SubscriptionStatus::{$dbData['status']}());
        $subscription->setInstallments($dbData['installments']);
        $subscription->setPaymentMethod(PaymentMethod::{$dbData['payment_method']}());
        $subscription->setIntervalType($dbData['interval_type']);
        $subscription->setIntervalCount($dbData['interval_count']);
        $subscription->setCreatedAt($dbData['created_at']);
        $subscription->setUpdatedAt($dbData['updated_at']);

        $subscription->setPlatformOrder($this->getPlatformOrder($dbData['code']));

        $subscription->setPlugId(new SubscriptionId($dbData['plug_id']));

        if (isset($dbData['current_cycle'])) {
            $cycleFactory = new CycleFactory();
            $cycle = $cycleFactory->createFromPostData($dbData['current_cycle']);
            $subscription->setCurrentCycle($cycle);
        }

        if (isset($dbData['current_charge'])) {
            $chargeFactory = new ChargeFactory();
            $charge = $chargeFactory->createFromPostData($dbData['current_charge']);
            $subscription->setCurrentCharge($charge);
        }

        if (!empty($dbData['plan_id'])) {
            $subscription->setPlanId(new PlanId($dbData['plan_id']));
        }

        return $subscription;
    }

    /**
     * @param $subscriptionResponse
     * @return Subscription
     * @throws InvalidParamException
     */
    public function createFromFailedSubscription($subscriptionResponse)
    {
        $subscription = new Subscription();

        $subscription->setCode($subscriptionResponse['code']);

        $subscriptionId = new SubscriptionId($subscriptionResponse['id']);
        $subscription->setPlugId($subscriptionId);

        return $subscription;
    }

    private function setCurrentCharge($postData, & $subscription)
    {
        if (isset($postData['current_charge'])) {
            $currentCharge = $postData['current_charge'];

            if (!$currentCharge instanceof Charge) {
                $currentCharge = json_decode(json_encode($currentCharge), true);
                $chargeFactory = new ChargeFactory();
                $currentCharge = $chargeFactory->createFromPostData($currentCharge);
            }

            $subscription->setCurrentCharge($currentCharge);
        }
    }

    private function setCustomer($postData, & $subscription)
    {
        if (isset($postData['customer'])) {

            $customerFactory = new CustomerFactory();
            $customer = $customerFactory->createFromPostData($postData['customer']);

            $subscription->setCustomer($customer);
        }
    }

    private function setCurrentCycle($postData, & $subscription)
    {
        if (isset($postData['current_cycle'])) {
            $cycleFactory = new CycleFactory();
            $cycle = $cycleFactory->createFromPostData($postData['current_cycle']);
            $subscription->setCurrentCycle($cycle);
        }
    }
}
