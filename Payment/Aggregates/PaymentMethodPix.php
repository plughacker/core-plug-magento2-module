<?php

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreatePaymentMethodPixRequest;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use PlugHacker\PlugCore\Payment\Traits\WithAmountTrait;

final class PaymentMethodPix extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    use WithAmountTrait;

    /** @var string */
    private $paymentType;

    /** @var int */
    private $expiresIn;

    /**
     * @return string
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
    }

    /**
     * @return int
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * @param int $expiresIn
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;
    }

    public function jsonSerialize(): mixed
    {
        $obj = new \stdClass();
        $obj->paymentType = $this->paymentType;
        $obj->expiresIn = $this->expiresIn;
        return $obj;
    }

    /**
     * @return CreatePaymentMethodPixRequest
     */
    public function convertToSDKRequest()
    {
        $paymentMethodRequest = new CreatePaymentMethodPixRequest();
        $paymentMethodRequest->paymentType = $this->getPaymentType();
        $paymentMethodRequest->expiresIn = $this->getExpiresIn();

        return $paymentMethodRequest;
    }
}
