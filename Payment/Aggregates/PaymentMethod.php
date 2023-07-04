<?php

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreatePaymentMethodRequest;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use PlugHacker\PlugCore\Payment\Traits\WithAmountTrait;

final class PaymentMethod extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    use WithAmountTrait;

    /** @var string */
    private $paymentType;

    /** @var string */
    private $installments;

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
    public function getInstallments()
    {
        return $this->installments;
    }

    /**
     * @param string $installments
     */
    public function setInstallments($installments)
    {
        $this->installments = $installments;
    }

    public function jsonSerialize(): mixed
    {
        $obj = new \stdClass();
        $obj->paymentType = $this->paymentType;
        $obj->installments = $this->installments;
        return $obj;
    }

    /**
     * @return CreatePaymentMethodRequest
     */
    public function convertToSDKRequest()
    {
        $paymentMethodRequest = new CreatePaymentMethodRequest();
        $paymentMethodRequest->paymentType = $this->getPaymentType();
        $paymentMethodRequest->installments = $this->getInstallments();
        return $paymentMethodRequest;
    }
}
