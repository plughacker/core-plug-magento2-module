<?php

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreatePaymentSourceBoletoRequest;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use PlugHacker\PlugCore\Payment\Traits\WithAmountTrait;

final class CustomerPaymentSource extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    use WithAmountTrait;

    /** @var string */
    private $sourceType;

    /** @var null|CustomerBoleto */
    private $customer;

    /**
     * @return string
     */
    public function getSourceType()
    {
        return $this->sourceType;
    }

    /**
     * @param string $sourceType
     */
    public function setSourceType($sourceType)
    {
        $this->sourceType = $sourceType;
    }

    /**
     * @return CustomerBoleto|null
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param CustomerBoleto|null $customer
     */
    public function setCustomer(CustomerBoleto $customer)
    {
        $this->customer = $customer;
    }

    public function jsonSerialize()
    {
        $obj = new \stdClass();
        $obj->sourceType = $this->sourceType;
        $obj->customer = $this->customer;
        return $obj;
    }

    /**
     * @return CreatePaymentSourceBoletoRequest
     */
    public function convertToSDKRequest()
    {
        $paymentMethodRequest = new CreatePaymentSourceBoletoRequest();
        $paymentMethodRequest->sourceType = $this->getSourceType();
        $paymentMethodRequest->customer = $this->getCustomer();
        return $paymentMethodRequest;
    }
}
