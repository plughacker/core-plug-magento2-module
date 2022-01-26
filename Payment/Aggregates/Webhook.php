<?php

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreateWebhookRequest;
use PlugHacker\PlugAPILib\Models\CreatePaymentMethodRequest;
use PlugHacker\PlugAPILib\Models\CreatePaymentSourceRequest;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Payment\Aggregates\Payments\AbstractPayment;
use PlugHacker\PlugCore\Payment\Aggregates\Payments\SavedCreditCardPayment;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use PlugHacker\PlugCore\Payment\Traits\WithAmountTrait;
use PlugHacker\PlugCore\Payment\Traits\WithCustomerTrait;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use PlugHacker\PlugCore\Kernel\ValueObjects\PaymentMethod as PaymentMethod;

final class Webhook extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    /** @var string */
    private $event;

    /** @var string */
    private $endpoint;

    /** @var int */
    private $version;

    /** @var string */
    private $status;

    public function __construct()
    {
        $this->version = 1;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param string $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->event = $this->getEvent();
        $obj->endpoint = $this->getEndpoint();
        $obj->version = $this->getVersion();
        $obj->status = $this->getStatus();
        return $obj;
    }

    /**
     * @return CreateWebhookRequest
     */
    public function convertToSDKRequest()
    {
        $orderRequest = new CreateWebhookRequest();
        $orderRequest->event = $this->getEvent();
        $orderRequest->endpoint = $this->getEndpoint();
        $orderRequest->version = $this->getVersion();
        $orderRequest->status = $this->getStatus();

        return $orderRequest;
    }
}
