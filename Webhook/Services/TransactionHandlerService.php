<?php
declare(strict_types=1);

namespace PlugHacker\PlugCore\Webhook\Services;

use PlugHacker\PlugCore\Kernel\Repositories\OrderRepository;
use PlugHacker\PlugCore\Kernel\ValueObjects\OrderStatus;
use PlugHacker\PlugCore\Payment\Services\ResponseHandlers\OrderHandler;
use PlugHacker\PlugCore\Webhook\Aggregates\Webhook;

class TransactionHandlerService extends AbstractHandlerService
{
    protected function loadOrder(Webhook $webhook)
    {
        $orderRepository = new OrderRepository();

        $this->order = $orderRepository->findByPlatformId($webhook->getEntity()->getId());
    }

    protected function handlePending($webhook)
    {
        $orderHandler = $this->getOrderHandler();

        $orderHandler->handle($this->order);
    }

    protected function handlePreAuthorized($webhook)
    {
        $orderHandler = $this->getOrderHandler();

        $status = $webhook->getEntity()->getStatus()->getStatus();

        $this->order->setStatus(OrderStatus::$status());

        $orderHandler->handle($this->order);
    }

    protected function handleAuthorized($webhook)
    {
        $orderHandler = $this->getOrderHandler();

        $status = $webhook->getEntity()->getStatus()->getStatus();

        $this->order->setStatus(OrderStatus::$status());

        $orderHandler->handle($this->order);
    }

    protected function handleCanceled($webhook) {
        $orderHandler = $this->getOrderHandler();

        $status = $webhook->getEntity()->getStatus()->getStatus();

        $this->order->setStatus(OrderStatus::$status());

        $orderHandler->handle($this->order);
    }

    protected function handleVoided($webhook) {
        $orderHandler = $this->getOrderHandler();

        $status = $webhook->getEntity()->getStatus()->getStatus();

        $this->order->setStatus(OrderStatus::$status());

        $orderHandler->handle($this->order);
    }

    protected function handleChargedBack($webhook) {
        $orderHandler = $this->getOrderHandler();

        $status = $webhook->getEntity()->getStatus()->getStatus();

        $baseStatus = explode('_', $status);

        $status = $baseStatus[0];

        for ($i = 1, $iMax = count($baseStatus); $i < $iMax; $i++) {
            $status .= ucfirst(($baseStatus[$i]));
        }

        $this->order->setStatus(OrderStatus::$status());

        $orderHandler->handle($this->order);
    }

    protected function getOrderHandler(): OrderHandler
    {
        return new OrderHandler();
    }
}
