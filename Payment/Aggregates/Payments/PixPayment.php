<?php

namespace PlugHacker\PlugCore\Payment\Aggregates\Payments;

use PlugHacker\PlugAPILib\Models\CreatePaymentMethodPixRequest;
use PlugHacker\PlugAPILib\Models\CreatePixPaymentRequest;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Payment\Aggregates\CustomerBoleto;
use PlugHacker\PlugCore\Payment\Aggregates\CustomerPaymentSource;
use PlugHacker\PlugCore\Payment\Aggregates\PaymentMethodPix;
use PlugHacker\PlugCore\Payment\ValueObjects\PaymentMethod;

final class PixPayment extends AbstractPayment
{
    /**
     * @var integer|null $expiresIn
     */
    public $expiresIn;

    /**
     * @var \DateTime|null $expiresAt
     */
    public $expiresAt;

    /**
     * @var array $additionalInformation
     */
    public $additionalInformation;

    /**
     * @var PaymentMethodPix $paymentMethod
     */
    public $paymentMethod;

    /**
     * @var CustomerPaymentSource $paymentSource
     */
    public $paymentSource;


    /**
     * @return int|null
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * @param int|null $expiresIn
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @param \DateTime|null $expiresAt
     */
    public function setExpiresAt(\DateTime $expiresAt)
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return array
     */
    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
    }

    /**
     * @param array $additionalInformation
     */
    public function setAdditionalInformation($additionalInformation)
    {
        $this->additionalInformation = $additionalInformation;
    }

    /**
     * @return PaymentMethodPix
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param PaymentMethodPix $paymentMethod
     */
    public function setPaymentMethod(PaymentMethodPix $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return CustomerPaymentSource
     */
    public function getPaymentSource()
    {
        return $this->paymentSource;
    }

    /**
     * @param CustomerPaymentSource $paymentSource
     */
    public function setPaymentSource(CustomerPaymentSource $paymentSource)
    {
        $this->paymentSource = $paymentSource;
    }

    public function jsonSerialize(): mixed
    {
        $obj = parent::jsonSerialize();
        $obj->expiresIn = $this->getExpiresIn();
        $obj->expiresAt = $this->getExpiresAt();
        $obj->paymentMethod = $this->getPaymentMethod();
        $obj->paymentSource = $this->getPaymentSource();

        return $obj;
    }

    static public function getBaseCode()
    {
        return PaymentMethod::pix()->getMethod();
    }

    /**
     * @return CreatePixPaymentRequest
     */
    protected function convertToPrimitivePaymentRequest()
    {
        $paymentRequest = new CreatePixPaymentRequest();

        $paymentRequest->expiresIn = $this->getExpiresIn();
        $paymentRequest->expiresAt = $this->getExpiresAt();
        $paymentRequest->additionalInformation= $this->getAdditionalInformation();
        $paymentRequest->capture = true;

        $paymentMethod = new PaymentMethodPix();
        $paymentMethod->setPaymentType('pix');
        $paymentMethod->setExpiresIn((int)$this->getExpiresIn());
        $paymentRequest->paymentMethod = $paymentMethod->convertToSDKRequest();

        $customer = $this->getCustomer();
        $customerBoleto = new CustomerBoleto();
        $customerBoleto->setName((string)$customer->getName());
        $customerBoleto->setEmail((string)$customer->getEmail());
        $customerBoleto->setPhoneNumber((string)$customer->getPhoneNumber());
        $customerBoleto->setDocument($customer->getDocument());

        $paymentSource = new CustomerPaymentSource();
        $paymentSource->setSourceType('customer');
        $paymentSource->setCustomer($customerBoleto);
        $paymentRequest->paymentSource = $paymentSource->convertToSDKRequest();

        return $paymentRequest;
    }
}
