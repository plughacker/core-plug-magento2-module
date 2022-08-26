<?php

namespace PlugHacker\PlugCore\Payment\Services;

use PlugHacker\PlugCore\Kernel\Repositories\OrderRepository;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\OrderId;

class OrderService
{
    public function getPixQrCodeInfoFromOrder(OrderId $orderId)
    {
        $orderRepository = new OrderRepository();
        $order = $orderRepository->findByPlugId(new OrderId($orderId));
        $qrCodeInfo = [];

        if ($order !== null) {
            $charges = $order->getCharges();
            foreach ($charges as $charge) {
                $qrCodeInfo = $this->getInfoFromCharge($charge);
            }
        }

        return $qrCodeInfo;
    }

    private function getInfoFromCharge($charge)
    {
        $transaction = $charge->getTransactionRequests();
        $postData = $transaction->getPostData();
        $data = json_decode($postData->tran_data, true);
        if (!empty($data['qrCodeData']) && !empty($data['qrCodeImageUrl'])) {
            $qrCodeInfo['qrCodeData'] = $data['qrCodeData'];
            $qrCodeInfo['qrCodeImageUrl'] = $data['qrCodeImageUrl'];
            return $qrCodeInfo;
        }

        return [];
    }
}
