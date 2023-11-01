<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractValueObject;

final class PaymentMethod extends AbstractValueObject
{
    const CREDIT_CARD = 'credit_card';
    const BOLETO = 'boleto';
    const PIX = 'pix';

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * PaymentMethod constructor.
     *
     * @param string $paymentMethod
     */
    private function __construct($paymentMethod)
    {
        $this->setPaymentMethod($paymentMethod);
    }

    static public function credit_card()
    {
        return new self(self::CREDIT_CARD);
    }

    static public function boleto()
    {
        return new self(self::BOLETO);
    }

    static public function pix()
    {
        return new self(self::PIX);
    }

    /**
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     *
     * @param string $paymentMethod
     * @return PaymentMethod
     */
    private function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param ChargeStatus $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return $this->getStatus() === $object->getStatus();
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link   https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since  5.4.0
     */
    public function jsonSerialize(): mixed
    {
        return $this->paymentMethod;
    }
}
