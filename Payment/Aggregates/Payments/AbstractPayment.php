<?php

namespace PlugHacker\PlugCore\Payment\Aggregates\Payments;

use PlugHacker\PlugAPILib\Models\CreatePaymentRequest;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use PlugHacker\PlugCore\Payment\Interfaces\HaveOrderInterface;
use PlugHacker\PlugCore\Payment\Traits\WithAmountTrait;
use PlugHacker\PlugCore\Payment\Traits\WithCartItemsTrait;
use PlugHacker\PlugCore\Payment\Traits\WithCustomerTrait;
use PlugHacker\PlugCore\Payment\Traits\WithOrderTrait;

abstract class AbstractPayment
    extends AbstractEntity
    implements ConvertibleToSDKRequestsInterface, HaveOrderInterface
{
    use WithAmountTrait;
    use WithCartItemsTrait;
    use WithCustomerTrait;
    use WithOrderTrait;

    public function jsonSerialize(): mixed
    {
        $obj = new \stdClass();

        $obj->orderCode = $this->order->getCode();
        $obj->paymentMethod = static::getBaseCode();
        $obj->amount = $this->getAmount();

        $customer = $this->getCustomer();
        if ($customer !== null) {
            $obj->customer = $customer;
        }

        return $obj;
    }

    abstract static public function getBaseCode();

    /**
     * @return CreatePaymentRequest
     */
    public function convertToSDKRequest()
    {
        $newPayment = new CreatePaymentRequest();
        $newPayment->amount = $this->getAmount();

        $primitive = static::getBaseCode();
        $newPayment->$primitive = $this->convertToPrimitivePaymentRequest();
        $newPayment->paymentMethod = $this->cammel2SnakeCase($primitive);

        if ($this->getCustomer() !== null) {
            $newPayment->customer = $this->getCustomer()->convertToSDKRequest();
        }

        $newPayment->metadata = static::getMetadata();
        return $newPayment;
    }

    abstract protected function convertToPrimitivePaymentRequest();

    protected function getMetadata()
    {
        return null;
    }

    private function cammel2SnakeCase($cammelCaseString)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $cammelCaseString, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }
}
