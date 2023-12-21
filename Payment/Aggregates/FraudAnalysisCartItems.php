<?php
declare(strict_types=1);

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreateFraudAnalysisCartItemsRequest;
use PlugHacker\PlugAPILib\Models\CreateFraudAnalysisCustomerBillingAddressRequest;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;

class FraudAnalysisCartItems implements ConvertibleToSDKRequestsInterface
{
    public string $name;
    public int $quantity;
    public string $sku;
    public int $unitPrice;
    public string $risk;
    public string $description;
    public string $categoryId;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function setSku(string $sku): void
    {
        $this->sku = $sku;
    }

    public function getUnitPrice(): int
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(int $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }

    public function getRisk(): string
    {
        return $this->risk;
    }

    public function setRisk(string $risk): void
    {
        $this->risk = $risk;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    public function setCategoryId(string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function convertToSDKRequest()
    {
        $fraudAnalysisCartItems = new CreateFraudAnalysisCartItemsRequest();
        $fraudAnalysisCartItems->name = $this->getName();
        $fraudAnalysisCartItems->quantity = $this->getQuantity();
        $fraudAnalysisCartItems->sku = $this->getSku();
        $fraudAnalysisCartItems->unitPrice = $this->getUnitPrice();
        $fraudAnalysisCartItems->risk = $this->getRisk();
        $fraudAnalysisCartItems->description = $this->getDescription();
        $fraudAnalysisCartItems->categoryId = $this->getCategoryId();

        return $fraudAnalysisCartItems;
    }
}
