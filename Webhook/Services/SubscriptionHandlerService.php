<?php

namespace PlugHacker\PlugCore\Webhook\Services;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use PlugHacker\PlugCore\Kernel\Aggregates\Order;
use PlugHacker\PlugCore\Kernel\Exceptions\NotFoundException;
use PlugHacker\PlugCore\Kernel\Factories\OrderFactory;
use PlugHacker\PlugCore\Kernel\Interfaces\PlatformOrderInterface;
use PlugHacker\PlugCore\Kernel\Repositories\OrderRepository;
use PlugHacker\PlugCore\Kernel\Services\APIService;
use PlugHacker\PlugCore\Kernel\Services\LocalizationService;
use PlugHacker\PlugCore\Kernel\Services\OrderService;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\SubscriptionId;
use PlugHacker\PlugCore\Recurrence\Aggregates\Charge;
use PlugHacker\PlugCore\Webhook\Aggregates\Webhook;
use PlugHacker\PlugCore\Recurrence\Aggregates\Subscription;
use PlugHacker\PlugCore\Recurrence\Repositories\SubscriptionRepository;

class SubscriptionHandlerService extends AbstractHandlerService
{
    protected function handleCreated(Webhook $webhook)
    {
        throw new NotFoundException('Webhook Not implemented');
    }

    protected function handleCanceled(Webhook $webhook)
    {
        $subscriptionRepository = new SubscriptionRepository();
        $orderService = new OrderService();
        $i18n = new LocalizationService();
        $orderFactory = new OrderFactory();

        /**
         * @var Subscription
         */
        $subscription = $webhook->getEntity();

        $this->order->setStatus($subscription->getStatus());

        $subscriptionRepository->save($this->order);

        $history = $i18n->getDashboard('Subscription canceled');
        $this->order->getPlatformOrder()->addHistoryComment($history);

        $platformOrderStatus = ucfirst(
            $this->order->getPlatformOrder()
                ->getPlatformOrder()
                ->getStatus()
        );

        $realOrder = $orderFactory->createFromSubscriptionData(
            $this->order,
            $platformOrderStatus
        );

        $orderService->syncPlatformWith($realOrder);

        $result = [
            "message" => 'Subscription cancel registered',
            "code" => 200
        ];

        return $result;
    }

    public function loadOrder(Webhook $webhook)
    {
        $subscriptionRepository = new SubscriptionRepository();
        $apiService = new ApiService();

        $subscriptionId = $webhook->getEntity()->getSubscriptionId()->getValue();
        $subscriptionObject = $apiService->getSubscription(new SubscriptionId($subscriptionId));

        if (!$subscriptionObject) {
            throw new Exception('Code not found.', 400);
        }

        $subscription = $subscriptionRepository->findByCode($subscriptionObject->getCode());
        if ($subscription === null) {
            $code = $subscriptionObject->getCode();
            throw new NotFoundException("Subscription #{$code} not found.");
        }

        $this->order = $subscription;
    }
}
