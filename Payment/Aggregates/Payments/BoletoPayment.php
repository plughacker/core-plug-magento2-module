<?php

namespace PlugHacker\PlugCore\Payment\Aggregates\Payments;

use PlugHacker\PlugAPILib\Models\CreateBoletoPaymentRequest;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException;
use PlugHacker\PlugCore\Payment\Aggregates\Customer;
use PlugHacker\PlugCore\Payment\Aggregates\CustomerPaymentSource;
use PlugHacker\PlugCore\Payment\Aggregates\CustomerBoleto;
use PlugHacker\PlugCore\Payment\ValueObjects\AbstractCardIdentifier;
use PlugHacker\PlugCore\Payment\ValueObjects\BoletoBank;
use PlugHacker\PlugCore\Payment\ValueObjects\PaymentMethod;

final class BoletoPayment extends AbstractPayment
{
    /** @var BoletoBank */
    private $bank;

    /** @var string */
    private $instructions;

    /** @var string */
    private $expiresDate;

    /**
     * @return BoletoBank
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @param BoletoBank $bank
     */
    public function setBank(BoletoBank $bank)
    {
        $this->bank = $bank;
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

    public function jsonSerialize()
    {
        $obj = parent::jsonSerialize();

        $obj->bank = $this->bank;
        $obj->instructions = $this->instructions;

        return $obj;
    }

    static public function getBaseCode()
    {
        return PaymentMethod::boleto()->getMethod();
    }

    /**
     * @return CreateBoletoPaymentRequest
     */
    protected function convertToPrimitivePaymentRequest()
    {
        $paymentRequest = new CreateBoletoPaymentRequest();

        $paymentRequest->bank = $this->getBank()->getCode();
        $paymentRequest->instructions = $this->getInstructions();
        $paymentRequest->capture = true;

        $paymentMethod = new \PlugHacker\PlugCore\Payment\Aggregates\PaymentMethodBoleto();
        $paymentMethod->setPaymentType('boleto');
        $paymentMethod->setExpiresDate($this->getExpiresDate());
        $paymentMethod->setInstructions($this->getInstructions());
        $paymentRequest->paymentMethod = $paymentMethod->convertToSDKRequest();

        $customer = $this->getCustomer();
        $customerBoleto = new \PlugHacker\PlugCore\Payment\Aggregates\CustomerBoleto();
        $customerBoleto->setName($customer->getName());
        $customerBoleto->setEmail($customer->getEmail());
        $customerBoleto->setPhoneNumber($customer->getPhoneNumber());
        $customerBoleto->setDocument($customer->getDocument());

        $paymentSource = new CustomerPaymentSource();
        $paymentSource->setSourceType('customer');
        $paymentSource->setCustomer($customerBoleto);
        $paymentRequest->paymentSource = $paymentSource->convertToSDKRequest();

        return $paymentRequest;
    }
}
