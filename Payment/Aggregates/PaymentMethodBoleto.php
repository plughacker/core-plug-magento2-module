<?php

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreatePaymentMethodBoletoRequest;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use PlugHacker\PlugCore\Payment\Traits\WithAmountTrait;

final class PaymentMethodBoleto extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    use WithAmountTrait;

    /** @var string */
    private $paymentType;

    /** @var string */
    private $expiresDate;

    /** @var string */
    private $instructions;

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
     * @return string
     */
    public function getExpiresDate()
    {
        return $this->expiresDate;
    }

    /**
     * @param string $expiresDate
     */
    public function setExpiresDate($expiresDate)
    {
        $this->expiresDate = $expiresDate;
    }

    /**
     * @return string
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * @param string $instructions
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;
    }

    public function jsonSerialize(): mixed
    {
        $obj = new \stdClass();
        $obj->paymentType = $this->paymentType;
        $obj->expiresDate = $this->expiresDate;
        $obj->instructions = $this->instructions;
        return $obj;
    }

    /**
     * @return CreatePaymentMethodBoletoRequest
     */
    public function convertToSDKRequest()
    {
        $paymentMethodRequest = new CreatePaymentMethodBoletoRequest();
        $paymentMethodRequest->paymentType = $this->getPaymentType();
        $paymentMethodRequest->expiresDate = $this->getExpiresDate();
        $paymentMethodRequest->instructions = $this->getInstructions();
        return $paymentMethodRequest;
    }
}
