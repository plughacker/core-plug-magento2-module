<?php

namespace PlugHacker\PlugCore\Payment\Aggregates\Payments;

use PlugHacker\PlugAPILib\Models\CreateCardRequest;
use PlugHacker\PlugAPILib\Models\CreateCreditCardPaymentRequest;
use PlugHacker\PlugAPILib\Models\CreateFraudAnalysisCartItemsRequest;
use PlugHacker\PlugAPILib\Models\CreateFraudAnalysisCartRequest;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException;
use PlugHacker\PlugCore\Kernel\Services\InstallmentService;
use PlugHacker\PlugCore\Kernel\Services\MoneyService;
use PlugHacker\PlugCore\Kernel\ValueObjects\CardBrand;
use PlugHacker\PlugCore\Payment\Aggregates\CartItems;
use PlugHacker\PlugCore\Payment\Aggregates\FraudAnalysis;
use PlugHacker\PlugCore\Payment\Aggregates\FraudAnalysisCart;
use PlugHacker\PlugCore\Payment\Aggregates\FraudAnalysisCartItems;
use PlugHacker\PlugCore\Payment\Aggregates\FraudAnalysisCustomer;
use PlugHacker\PlugCore\Payment\Aggregates\FraudAnalysisCustomerBillingAddress;
use PlugHacker\PlugCore\Payment\Aggregates\FraudAnalysisCustomerBrowser;
use PlugHacker\PlugCore\Payment\Aggregates\FraudAnalysisCustomerDeliveryAddress;
use PlugHacker\PlugCore\Payment\ValueObjects\AbstractCardIdentifier;
use PlugHacker\PlugCore\Payment\ValueObjects\PaymentMethod;

abstract class AbstractCreditCardPayment extends AbstractPayment
{
    /** @var CardBrand */
    protected $brand;
    /** @var int */
    protected $installments;
    /** @var string */
    protected $statementDescriptor;
    /** @var boolean */
    protected $capture;
    /** @var AbstractCardIdentifier */
    protected $identifier;

    protected $fraudAnalysis;
    public function __construct()
    {
        $this->installments = 1;
        $this->capture = true;
    }

    /**
     * @return int
     */
    public function getInstallments()
    {
        return $this->installments;
    }

    /**
     * @param int $installments
     */
    public function setInstallments($installments)
    {
        if ($installments < 1) {
            throw new InvalidParamException(
                "Installments should be at least 1",
                $installments
            );
        }

        $installmentsEnabled = MPSetup::getModuleConfiguration()
            ->isInstallmentsEnabled();

        if (!$installmentsEnabled && $installments > 1) {
            throw new InvalidParamException(
                "Trying to set installment number greater than 1 when installments is disabled!",
                $installments
            );
        }

        //amount defined?
        if ($this->amount === null) {
            throw new \Exception(
                "Amount must be defined before adding installments",
                400
            );
        }

        //brand added?
        if ($this->brand === null) {
            throw new \Exception(
                "Card brand must be defined before adding installments",
                400
            );
        }

        //check if the installment is applicable to brand, value and (@todo) order;
        $this->validateIfIsRealInstallment($installments);

        $this->installments = $installments;
    }
    /**
     * @return bool
     */
    private function validateIfIsRealInstallment($installments)
    {
        //get valid installments for this brand.
        $installmentService = new InstallmentService();
        $validInstallments = $installmentService->getInstallmentsFor(
            null,
            $this->brand,
            $this->amount
        );

        //check each installemnt
        foreach ($validInstallments as $validInstallment) {
            if ($validInstallment->getTimes() === $installments) {
                return;
            }
        }

        //invalid installment
        $moneyService = new MoneyService();
        $exception = "The card brand '%s' or the amount %.2f doesn't allow the %dx installments!";
        $exception = sprintf(
            $exception,
            $this->brand->getName(),
            $moneyService->centsToFloat($this->amount),
            $installments
        );
        throw new InvalidParamException(
            $exception,
            $installments
        );
    }

    /**
     * @return string
     */
    public function getStatementDescriptor()
    {
        return $this->statementDescriptor;
    }

    /**
     * @param string $statementDescriptor
     */
    public function setStatementDescriptor($statementDescriptor)
    {
        $this->statementDescriptor = $statementDescriptor;
    }

    public function getFraudAnalysis()
    {
        return $this->fraudAnalysis;
    }

    public function setFraudAnalysis(bool $fraudAnalysis)
    {
        $this->fraudAnalysis = $fraudAnalysis;
    }

    /**
     * @return bool
     */
    public function isCapture()
    {
        return $this->capture;
    }

    /**
     * @param bool $capture
     */
    public function setCapture($capture)
    {
        $this->capture = $capture;
    }

    /**
     * @return AbstractCardIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return CardBrand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param CardBrand $brand
     */
    public function setBrand(CardBrand $brand)
    {
        //@todo A inactive card brand should be a valid brand for this agg?
        $this->brand = $brand;
    }

    public function jsonSerialize(): mixed
    {
        $obj =  parent::jsonSerialize();

        $obj->installments = $this->installments;
        $obj->brand = $this->brand;
        $obj->statementDescriptor = $this->statementDescriptor;
        $obj->capture = $this->capture;
        $obj->identifier = $this->identifier;
        $obj->fraudAnalysis = $this->fraudAnalysis;

        return $obj;
    }


    /**
     * @param AbstractCardIdentifier $identifier
     */
    abstract public function setIdentifier(AbstractCardIdentifier $identifier);

    static public function getBaseCode()
    {
        return PaymentMethod::creditCard()->getMethod();
    }

    /**
     * @return CreateCreditCardPaymentRequest
     */
    protected function convertToPrimitivePaymentRequest()
    {
        $createCardRequest = new CreateCardRequest();
        $customer = $this->getCustomer();
        $createCardRequest->billingAddress = $customer->getAddressToSDK();

        $cardRequest = new CreateCreditCardPaymentRequest();
        $cardRequest->card = $createCardRequest;
        $cardRequest->capture = $this->isCapture();
        $cardRequest->installments = $this->getInstallments();
        $cardRequest->statementDescriptor = $this->getStatementDescriptor();

        if ($this->getFraudAnalysis()) {
            $billingAddress = $customer->getBillingAddress();

            $fraudAnalysisCustomerBillingAddress = new FraudAnalysisCustomerBillingAddress();
            $fraudAnalysisCustomerBillingAddress->setStreet((string)$billingAddress->getStreet());
            $fraudAnalysisCustomerBillingAddress->setNumber((string)$billingAddress->getNumber());
            $fraudAnalysisCustomerBillingAddress->setComplement((string)$billingAddress->getComplement());
            $fraudAnalysisCustomerBillingAddress->setDistrict((string)$billingAddress->getNeighborhood());
            $fraudAnalysisCustomerBillingAddress->setZipCode((string)$billingAddress->getZipCode());
            $fraudAnalysisCustomerBillingAddress->setCity((string)$billingAddress->getCity());
            $fraudAnalysisCustomerBillingAddress->setState((string)$billingAddress->getState());
            $fraudAnalysisCustomerBillingAddress->setCountry((string)$billingAddress->getCountry());

            $deliveryAddress = $customer->getDeliveryAddress();

            $fraudAnalysisCustomerDeliveryAddress = new FraudAnalysisCustomerDeliveryAddress();
            $fraudAnalysisCustomerDeliveryAddress->setStreet((string)$deliveryAddress->getStreet());
            $fraudAnalysisCustomerDeliveryAddress->setNumber((string)$deliveryAddress->getNumber());
            $fraudAnalysisCustomerDeliveryAddress->setComplement((string)$deliveryAddress->getComplement());
            $fraudAnalysisCustomerDeliveryAddress->setDistrict((string)$deliveryAddress->getNeighborhood());
            $fraudAnalysisCustomerDeliveryAddress->setZipCode((string)$deliveryAddress->getZipCode());
            $fraudAnalysisCustomerDeliveryAddress->setCity((string)$deliveryAddress->getCity());
            $fraudAnalysisCustomerDeliveryAddress->setState((string)$deliveryAddress->getState());
            $fraudAnalysisCustomerDeliveryAddress->setCountry((string)$deliveryAddress->getCountry());

            $fraudAnalysisCustomerBrowser = new FraudAnalysisCustomerBrowser();

            $fraudAnalysisCustomerBrowser->setEmail((string)$customer->getEmail());
            $fraudAnalysisCustomerBrowser->setHostName($_SERVER['HTTP_HOST'] ??
                $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HOSTNAME'] ?? 'malga.io');

            $fraudAnalysisCustomerBrowser->setIpAddress($_SERVER['HTTP_CLIENT_IP'] ??
                $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

            $fraudAnalysisCustomerBrowser->setType((string)$_SERVER['HTTP_USER_AGENT']);

            $browserFingerprint = md5((string)$_SERVER['HTTP_SEC_CH_UA_PLATFORM'] .
                $fraudAnalysisCustomerBrowser->getType());

            $fraudAnalysisCustomerBrowser->setBrowserFingerprint($browserFingerprint);

            $fraudAnalysisCustomer = new FraudAnalysisCustomer();
            $fraudAnalysisCustomer->setName((string)$customer->getName());
            $fraudAnalysisCustomer->setEmail((string)$customer->getEmail());
            $fraudAnalysisCustomer->setPhone((string)$customer->getPhoneNumber());
            $fraudAnalysisCustomer->setIdentityType(mb_strtoupper((string)$customer->getDocument()?->getType()));
            $fraudAnalysisCustomer->setIdentity((string)$customer->getDocument()?->getNumber());
            $fraudAnalysisCustomer->setRegistrationDate((string)$customer->getRegistrationDate());
            $fraudAnalysisCustomer->setBillingAddress($fraudAnalysisCustomerBillingAddress->convertToSDKRequest());
            $fraudAnalysisCustomer->setDeliveryAddress($fraudAnalysisCustomerDeliveryAddress->convertToSDKRequest());
            $fraudAnalysisCustomer->setBrowser($fraudAnalysisCustomerBrowser->convertToSDKRequest());

            $fraudAnalysisCartItems = [];

            /** @var CartItems $cartItem */
            foreach ($this->getOrder()->getCart() as $cartItem) {
                $fraudAnalysisCartItem = new FraudAnalysisCartItems();
                $fraudAnalysisCartItem->setName($cartItem->getName());
                $fraudAnalysisCartItem->setQuantity($cartItem->getQuantity());
                $fraudAnalysisCartItem->setSku($cartItem->getSku());
                $fraudAnalysisCartItem->setUnitPrice($cartItem->getUnitPrice());
                $fraudAnalysisCartItem->setRisk($cartItem->getRisk());
                $fraudAnalysisCartItem->setDescription($cartItem->getDescription());
                $fraudAnalysisCartItem->setCategoryId($cartItem->getCategoryId());

                $fraudAnalysisCartItems[] = $fraudAnalysisCartItem->convertToSDKRequest();
            }

            $fraudAnalysisCart = new FraudAnalysisCart();
            $fraudAnalysisCart->setItems($fraudAnalysisCartItems);

            $fraudAnalysis = new FraudAnalysis();
            $fraudAnalysis->setCustomer($fraudAnalysisCustomer->convertToSDKRequest());
            $fraudAnalysis->setCart($fraudAnalysisCart->convertToSDKRequest());

            $cardRequest->fraudAnalysis = $fraudAnalysis->convertToSDKRequest();
        }

        $paymentMethod = new \PlugHacker\PlugCore\Payment\Aggregates\PaymentMethod();
        $paymentMethod->setPaymentType('credit');
        $paymentMethod->setInstallments($this->getInstallments());
        $cardRequest->paymentMethod = $paymentMethod->convertToSDKRequest();

        return $cardRequest;
    }
}
