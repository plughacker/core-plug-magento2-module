<?php
declare(strict_types=1);

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreateOrderCartItemsRequest;
use PlugHacker\PlugAPILib\Models\CreateOrderItemRequest;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;

final class CartItems implements ConvertibleToSDKRequestsInterface
{
    public const RISK_LOW = 'Low';

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

    public function jsonSerialize(): mixed
    {
        $object = new \stdClass();
        $object->name = $this->name;
        $object->quantity = $this->quantity;
        $object->sku = $this->sku;
        $object->unitPrice = $this->unitPrice;
        $object->risk = $this->risk;
        $object->description = $this->description;
        $object->categoryId = $this->categoryId;

        return $object;
    }

    /**
     * @return CreateOrderItemRequest
     */
    public function convertToSDKRequest()
    {
        $orderCartItems = new CreateOrderCartItemsRequest();
        $orderCartItems->name = $this->getName();
        $orderCartItems->quantity = $this->getQuantity();
        $orderCartItems->sku = $this->getSku();
        $orderCartItems->unitPrice = $this->getUnitPrice();
        $orderCartItems->risk = $this->getRisk();
        $orderCartItems->description = $this->getDescription();
        $orderCartItems->categoryId = $this->getCategoryId();

        return $orderCartItems;
    }
}
