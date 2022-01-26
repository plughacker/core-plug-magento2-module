<?php

namespace PlugHacker\PlugCore\Payment\Aggregates\Payments;

use PlugHacker\PlugAPILib\Models\CreateCreditCardPaymentRequest;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use PlugHacker\PlugCore\Payment\Aggregates\PaymentSource;
use PlugHacker\PlugCore\Payment\ValueObjects\AbstractCardIdentifier;
use PlugHacker\PlugCore\Payment\ValueObjects\CardToken;

class NewCreditCardPayment extends AbstractCreditCardPayment
{
    /** @var bool */
    private $saveOnSuccess;

    public function __construct()
    {
        $this->saveOnSuccess = false;
        parent::__construct();
    }

    /**
     * @return bool
     */
    public function isSaveOnSuccess()
    {
        $order = $this->getOrder();
        if ($order === null) {
            return false;
        }

        if (!MPSetup::getModuleConfiguration()->isSaveCards()) {
            return false;
        }

        $customer = $this->getCustomer();

        if ($customer === null) {
            return false;
        }

        return $this->saveOnSuccess;
    }

    /**
     * @param bool $saveOnSuccess
     */
    public function setSaveOnSuccess($saveOnSuccess)
    {
        $this->saveOnSuccess = boolval($saveOnSuccess);
    }

    public function jsonSerialize()
    {
        $obj = parent::jsonSerialize();

        $obj->cardToken = $this->identifier;

        return $obj;
    }

    public function setIdentifier(AbstractCardIdentifier $identifier)
    {
        $this->identifier = $identifier;
    }

    public function setCardToken(CardToken $cardToken)
    {
        $this->setIdentifier($cardToken);
    }

    /**
     * @return CreateCreditCardPaymentRequest
     */
    protected function convertToPrimitivePaymentRequest()
    {
        $paymentRequest = parent::convertToPrimitivePaymentRequest();

        $paymentRequest->cardToken = $this->getIdentifier()->getValue();

        $paymentSource = new PaymentSource();
        $paymentSource->setSourceType('token');
        $paymentSource->setTokenId($this->getIdentifier()->getValue());
        $paymentRequest->paymentSource = $paymentSource->convertToSDKRequest();

        return $paymentRequest;
    }

    protected function getMetadata()
    {
        $newCardMetadata = new \stdClass;

        $newCardMetadata->saveOnSuccess = $this->isSaveOnSuccess();

        return $newCardMetadata;
    }
}
