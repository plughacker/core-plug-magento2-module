<?php

namespace PlugHacker\PlugCore\Recurrence\Services\ResponseHandlers;

use \PlugHacker\PlugCore\Kernel\Aggregates\Charge;
use PlugHacker\PlugCore\Kernel\Factories\OrderFactory;
use PlugHacker\PlugCore\Payment\Aggregates\Customer;
use PlugHacker\PlugCore\Payment\Services\CardService;
use PlugHacker\PlugCore\Payment\Services\CustomerService;
use PlugHacker\PlugCore\Recurrence\Repositories\ChargeRepository;
use PlugHacker\PlugCore\Recurrence\Services\ResponseHandlers\AbstractResponseHandler;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractDataService;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use PlugHacker\PlugCore\Kernel\Aggregates\Order;
use PlugHacker\PlugCore\Kernel\Repositories\OrderRepository;
use PlugHacker\PlugCore\Kernel\Services\InvoiceService;
use PlugHacker\PlugCore\Kernel\Services\LocalizationService;
use PlugHacker\PlugCore\Kernel\Services\OrderService;
use PlugHacker\PlugCore\Kernel\ValueObjects\InvoiceState;
use PlugHacker\PlugCore\Kernel\ValueObjects\OrderState;
use PlugHacker\PlugCore\Kernel\ValueObjects\OrderStatus;
use PlugHacker\PlugCore\Kernel\ValueObjects\TransactionType;
use PlugHacker\PlugCore\Payment\Aggregates\Order as PaymentOrder;
use PlugHacker\PlugCore\Payment\Factories\SavedCardFactory;
use PlugHacker\PlugCore\Payment\Repositories\CustomerRepository;
use PlugHacker\PlugCore\Payment\Repositories\SavedCardRepository;
use PlugHacker\PlugCore\Recurrence\Aggregates\Subscription;
use PlugHacker\PlugCore\Recurrence\Factories\SubscriptionFactory;
use PlugHacker\PlugCore\Recurrence\Repositories\SubscriptionRepository;

final class SubscriptionHandler extends AbstractResponseHandler
{
    private $order;

    /**
     * @param Order $createdOrder
     * @return mixed
     */
    public function handle(Subscription $subscription)
    {
        $status = $this->getSubscriptionStatusFromCharge($subscription);
        $statusHandler = 'handleSubscriptionStatus' . $status;

        $platformOrderStatus = $status;

        $this->logService->orderInfo(
            $subscription->getCode(),
            "Handling subscription status: " . $status
        );
        $charge = $subscription->getCurrentCharge();
        $chargeRepository = new ChargeRepository();
        $chargeRepository->save($charge);

        $orderFactory = new OrderFactory();
        $this->order =
            $orderFactory->createFromSubscriptionData(
                $subscription,
                $platformOrderStatus
            );

        $subscriptionRepository = new SubscriptionRepository();
        $subscriptionRepository->save($subscription);

        $customerService = new CustomerService();
        $customerService->saveCustomer($subscription->getCustomer());

        return $this->$statusHandler($subscription);
    }

    private function handleSubscriptionStatusPaid(Subscription $subscription)
    {
        $invoiceService = new InvoiceService();
        $cardService = new CardService();

        $order = $this->order;

        $cantCreateReason = $invoiceService->getInvoiceCantBeCreatedReason($order);
        $platformInvoice = $invoiceService->createInvoiceFor($order);
        if ($platformInvoice !== null) {
            // create payment service to complete payment
            $this->completePayment($order, $subscription, $platformInvoice);

            $cardService->saveCards($order);

            return true;
        }
        return $cantCreateReason;
    }

    private function handleSubscriptionStatusPending(Subscription $subscription)
    {
        $order = $this->order;

        $order->setStatus(OrderStatus::pending());
        $platformOrder = $subscription->getPlatformOrder();

        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard(
                'Subscription created at Plug. Id: %s',
                $subscription->getPlugId()->getValue()
            )
        );

        $subscriptionRepository = new SubscriptionRepository();
        $subscriptionRepository->save($subscription);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);
        return true;
    }

    private function handleSubscriptionStatusFailed(Subscription $subscription)
    {
        $order = $this->order;

        $order->setStatus(OrderStatus::canceled());

        $platformOrder = $subscription->getPlatformOrder();
        $platformOrder->setState(OrderState::canceled());
        $platformOrder->save();

        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard(
                'Subscription payment failed at Plug. Id: %s',
                $subscription->getPlugId()->getValue()
            )
        );

        $subscriptionRepository = new SubscriptionRepository();
        $subscriptionRepository->save($subscription);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);

        $platformOrder->addHistoryComment(
            $i18n->getDashboard('Subscription canceled.')
        );

        return true;
    }

    private function handleSubscriptionStatus(Order $order)
    {
        $platformOrder = $order->getPlatformOrder();
        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard(
                'Order waiting for online retries at Plug.' .
                ' PlugId: ' . $order->getPlugId()->getValue()
            )
        );

        return $this->handleOrderStatusPending($order);
    }

    /**
     * @param Order $order
     * @param $invoice
     */
    private function completePayment(Order $order, Subscription $subscription, $invoice)
    {
        $invoice->setState(InvoiceState::paid());
        $invoice->save();
        $platformOrder = $order->getPlatformOrder();

        /**
         * @todo Check if we should create transactions
         */
        //$this->createCaptureTransaction($order);

        $order->setStatus(OrderStatus::processing());
        //@todo maybe an Order Aggregate should have a State too.
        $platformOrder->setState(OrderState::processing());

        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard('Subscription invoice paid.') . '<br>' .
            ' PlugId: ' . $subscription->getPlugId()->getValue() . '<br>' .
            $i18n->getDashboard('Invoice') . ': ' .
            $subscription->getInvoice()->getPlugId()->getValue()
        );

        $subscriptionRepository = new SubscriptionRepository();
        $subscriptionRepository->save($subscription);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);
    }

    private function getSubscriptionStatusFromCharge(Subscription $subscription)
    {
        $charge = $subscription->getCurrentCharge();
        return ucfirst($charge->getStatus()->getStatus());
    }
}
