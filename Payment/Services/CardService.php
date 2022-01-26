<?php

namespace PlugHacker\PlugCore\Payment\Services;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Aggregates\Order;
use PlugHacker\PlugCore\Kernel\Services\LogService;
use PlugHacker\PlugCore\Kernel\ValueObjects\CardBrand;
use PlugHacker\PlugCore\Kernel\ValueObjects\TransactionType;
use PlugHacker\PlugCore\Payment\Factories\SavedCardFactory;
use PlugHacker\PlugCore\Payment\Repositories\SavedCardRepository;

class CardService
{
    private $logService;

    public function __construct()
    {
        $this->logService = $this->getLogService();
    }

    public function getBrandsAvailables(AbstractEntity $config)
    {
        $brandsAvailables = [];
        $cardConfigs = $config->getCardConfigs();

        foreach ($cardConfigs as $cardConfig) {
            if (
                $cardConfig->isEnabled() &&
                !$cardConfig->getBrand()->equals(CardBrand::nobrand())
            ) {
                $brandsAvailables[] = $cardConfig->getBrand()->getName();
            }
        }

        return $brandsAvailables;
    }

    public function saveCards(Order $order)
    {
        $savedCardFactory = new SavedCardFactory();
        $savedCardRepository = new SavedCardRepository();
        $charges = $order->getCharges();

        foreach ($charges as $charge) {
            $transactionRequests = $charge->getTransactionRequests();
            if ($transactionRequests === null) {
                continue;
            }

            if (!$transactionRequests->getTransactionType()->equals(TransactionType::creditCard())) {
                continue; //save only credit card transactions;
            }

            $metadata = $charge->getMetadata();
            $saveOnSuccess =
                isset($metadata->saveOnSuccess) &&
                $metadata->saveOnSuccess === "true";

            if (
                !empty($transactionRequests->getCardData()) &&
                $saveOnSuccess &&
                $order->getCustomer()->getPlugId()->equals(
                    $charge->getCustomer()->getPlugId()
                )
            ) {
                $postData =
                    json_decode(json_encode($transactionRequests->getCardData()));
                $postData->owner =
                    $charge->getCustomer()->getPlugId();

                $savedCard = $savedCardFactory->createFromTransactionJson($postData);
                if (
                    $savedCardRepository->findByPlugId($savedCard->getPlugId()) === null
                ) {
                    $savedCardRepository->save($savedCard);
                    $this->logService->info(
                        $order->getCode(),
                        "Card '{$savedCard->getPlugId()->getValue()}' saved."
                    );

                }
            }
        }
    }

    public function getLogService()
    {
        return new LogService("Card Service", true);
    }
}
