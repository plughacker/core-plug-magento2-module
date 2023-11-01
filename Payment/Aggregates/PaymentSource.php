<?php

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreatePaymentSourceRequest;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use PlugHacker\PlugCore\Payment\Traits\WithAmountTrait;

final class PaymentSource extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    use WithAmountTrait;

    /** @var string */
    private $sourceType;

    /** @var string */
    private $tokenId;

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
     * @return string
     */
    public function getTokenId()
    {
        return $this->tokenId;
    }

    /**
     * @param string $tokenId
     */
    public function setTokenId($tokenId)
    {
        $this->tokenId = $tokenId;
    }

    public function jsonSerialize(): mixed
    {
        $obj = new \stdClass();
        $obj->sourceType = $this->sourceType;
        $obj->tokenId = $this->tokenId;
        return $obj;
    }

    /**
     * @return CreatePaymentSourceRequest
     */
    public function convertToSDKRequest()
    {
        $paymentMethodRequest = new CreatePaymentSourceRequest();
        $paymentMethodRequest->sourceType = $this->getSourceType();
        $paymentMethodRequest->tokenId = $this->getTokenId();
        return $paymentMethodRequest;
    }
}
