<?php

namespace PlugHacker\PlugCore\Payment\ValueObjects;

use PlugHacker\PlugAPILib\Models\CreateDocumentRequest;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;

final class CustomerDocument extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    /** @var string */
    private $number;

    /** @var string */
    private $type;

    /** @var string */
    private $country;

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function jsonSerialize()
    {
        $obj = new \stdClass();
        $obj->number = $this->number;
        $obj->type = $this->type;
        $obj->country = $this->country;
        return $obj;
    }

    /**
     * @return CreateDocumentRequest
     */
    public function convertToSDKRequest()
    {
        $paymentMethodRequest = new CreateDocumentRequest();
        $paymentMethodRequest->number = $this->getNumber();
        $paymentMethodRequest->type = $this->getType();
        $paymentMethodRequest->country = $this->getCountry() ?? 'BR';
        return $paymentMethodRequest;
    }
}
