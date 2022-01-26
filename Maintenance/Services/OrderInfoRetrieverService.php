<?php

namespace PlugHacker\PlugCore\Maintenance\Services;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use PlugHacker\PlugCore\Kernel\Interfaces\PlatformOrderInterface;
use PlugHacker\PlugCore\Kernel\Repositories\OrderRepository;
use PlugHacker\PlugCore\Maintenance\Interfaces\InfoRetrieverServiceInterface;

class OrderInfoRetrieverService implements InfoRetrieverServiceInterface
{
    public function retrieveInfo($value)
    {
        $orderInfo = new \stdClass();

        $orderInfo->core = $this->getCoreOrderInfo($value);
        $orderInfo->platform = $this->getPlatformOrderInfo($value);

        return $orderInfo;
    }


    private function getPlatformOrderInfo($orderIncrementId)
    {
        $platformOrderClass = MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);
        /**
         *
 * @var PlatformOrderInterface $platformOrder
*/
        $platformOrder = new $platformOrderClass();
        $platformOrder->loadByIncrementId($orderIncrementId);

        if ($platformOrder->getCode() === null) {
            return null;
        }

        $platformOrderInfo = new \stdClass();

        $platformOrderInfo->order = $platformOrder->getData();

        $platformOrderInfo->history = $platformOrder->getHistoryCommentCollection();
        $platformOrderInfo->transaction = $platformOrder->getTransactionCollection();
        $platformOrderInfo->payments = $platformOrder->getPaymentCollection();
        $platformOrderInfo->invoices = $platformOrder->getInvoiceCollection();

        return $platformOrderInfo;
    }

    private function getCoreOrderInfo($orderIncrementId)
    {
        $platformOrderClass = MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);
        /**
         *
 * @var PlatformOrderInterface $platformOrder
*/
        $platformOrder = new $platformOrderClass();
        $platformOrder->loadByIncrementId($orderIncrementId);

        if ($platformOrder->getCode() === null) {
            return null;
        }

        $plugOrderId = $platformOrder->getPlugId();

        if ($plugOrderId === null) {
            return null;
        }

        $orderRepository = new OrderRepository();

        $data = null;
        try {
            $data = $orderRepository->findByPlugId($plugOrderId);
        }catch (\Throwable $e)
        {
        }

        $coreOrder = new \stdClass();
        $coreOrder->mpOrderId = $plugOrderId;
        $coreOrder->data = $data;

        return $coreOrder;
    }
}
