<?php

namespace PlugHacker\PlugCore\Payment\Factories;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use PlugHacker\PlugCore\Kernel\Aggregates\Configuration;
use PlugHacker\PlugCore\Kernel\Services\InstallmentService;
use PlugHacker\PlugCore\Kernel\ValueObjects\CardBrand;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\CustomerId;
use PlugHacker\PlugCore\Payment\Aggregates\Customer;
use PlugHacker\PlugCore\Payment\Aggregates\Payments\AbstractCreditCardPayment;
use PlugHacker\PlugCore\Payment\Aggregates\Payments\BoletoPayment;
use PlugHacker\PlugCore\Payment\Aggregates\Payments\NewCreditCardPayment;
use PlugHacker\PlugCore\Payment\Aggregates\Payments\PixPayment;
use PlugHacker\PlugCore\Payment\Aggregates\Payments\SavedCreditCardPayment;
use PlugHacker\PlugCore\Payment\ValueObjects\BoletoBank;
use PlugHacker\PlugCore\Payment\ValueObjects\CardId;
use PlugHacker\PlugCore\Payment\ValueObjects\CardToken;
use PlugHacker\PlugCore\Payment\ValueObjects\CustomerType;
use PlugHacker\PlugCore\Payment\ValueObjects\PaymentMethod;

final class PaymentFactory
{
    /** @var string[] */
    private $primitiveFactories;

    /** @var Configuration  */
    private $moduleConfig;

    /** @var string */
    private $cardStatementDescriptor;

    /** @var BoletoBank */
    private $boletoBank;

    /** @var string */
    private $boletoInstructions;

    /** @var string */
    private $boletoExpirationDate;

    public function __construct()
    {
        $this->primitiveFactories = [
            'createCreditCardPayments',
            'createBoletoPayments',
            'createPixPayments',
        ];

        $this->moduleConfig = MPSetup::getModuleConfiguration();
        $this->cardStatementDescriptor = $this->moduleConfig->getCardStatementDescriptor();
        $this->boletoBank = BoletoBank::itau();
        $this->boletoInstructions = $this->moduleConfig->getBoletoInstructions();
        $this->boletoExpirationDate = $this->moduleConfig->getBoletoExpirationDate();
    }

    /**
     * @param $json
     * @return array
     */
    public function createFromJson($json)
    {
        $data = json_decode($json);
        $payments = [];
        foreach ($this->primitiveFactories as $creator) {
            $payments = array_merge($payments, $this->$creator($data));
        }

        return $payments;
    }

    /**
     * @param $data
     * @return array
     */
    private function createCreditCardPayments($data)
    {
        $cardDataIndex = AbstractCreditCardPayment::getBaseCode();
        if (!isset($data->$cardDataIndex)) {
            return [];
        }

        $cardsData = $data->$cardDataIndex;
        $payments = [];
        foreach ($cardsData as $cardData) {
            $payments[] = $this->createBasePayments($cardData, $cardDataIndex, $this->moduleConfig);
        }

        return $payments;
    }

    private function createBasePayments($cardData, $cardDataIndex, $config)
    {
        $payment = $this->createBaseCardPayment($cardData, $cardDataIndex);
        if ($payment === null) {
            return;
        }

        $customer = $this->createCustomer($cardData);
        if ($customer !== null) {
            $payment->setCustomer($customer);
        }

        $brand = $cardData->brand;
        $payment->setBrand(CardBrand::$brand());
        $payment->setAmount($cardData->amount);
        $payment->setInstallments($cardData->installments);

        //setting amount with interest
        $payment->setAmount($this->getAmountWithInterestForCreditCard($payment, $config));
        $payment->setCapture($config->isCapture());
        $payment->setStatementDescriptor($config->getCardStatementDescriptor());

        return $payment;
    }

    /**
     * @param $paymentData
     * @return Customer|null
     */
    private function createCustomer($paymentData)
    {
        if (empty($paymentData->customer)) {
            return null;
        }

        $customerFactory = new CustomerFactory();
        return $customerFactory->createFromJson(json_encode($paymentData->customer));
    }

    /**
     * @param AbstractCreditCardPayment $payment
     * @param $config
     * @return int
     * @throws \Exception
     */
    private function getAmountWithInterestForCreditCard(AbstractCreditCardPayment $payment, $config)
    {
        $installmentService = new InstallmentService();
        $validInstallments = $installmentService->getInstallmentsFor(
            null,
            $payment->getBrand(),
            $payment->getAmount(),
            $config
        );

        foreach ($validInstallments as $validInstallment) {
            if ($validInstallment->getTimes() === $payment->getInstallments()) {
                return $validInstallment->getTotal();
            }
        }

        throw new \Exception('Invalid installment number!');
    }

    /**
     * @param $data
     * @return array
     * @throws \PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException
     */
    private function createBoletoPayments($data)
    {
        $boletoDataIndex = BoletoPayment::getBaseCode();
        if (!isset($data->$boletoDataIndex)) {
            return [];
        }

        $boletosData = $data->$boletoDataIndex;

        $payments = [];
        foreach ($boletosData as $boletoData) {
            $payment = new BoletoPayment();
            $customer = $this->createCustomer($boletoData);
            if ($customer !== null) {
                $payment->setCustomer($customer);
            }

            $payment->setAmount($boletoData->amount);
            $payment->setBank($this->boletoBank);
            $payment->setInstructions($this->boletoInstructions);
            $payment->setInstructions('Instruções para pagamento do boleto');
            $boletoExpirationDate = \Date('Y-m-d', strtotime("+".$this->boletoExpirationDate." days"));
            $payment->setExpiresDate('2022-12-31');

            $payments[] = $payment;
        }

        return $payments;
    }

    /**
     * @param array $data
     * @return PixPayment[]
     * @throws InvalidParamException
     */
    private function createPixPayments($data)
    {
        $pixDataIndex = PixPayment::getBaseCode();

        if (!isset($data->$pixDataIndex)) {
            return [];
        }

        $pixData = $data->$pixDataIndex;

        $payments = [];
        foreach ($pixData as $value) {
            $payment = new PixPayment();

            $expiresIn = $this->moduleConfig->getPixConfig()->getExpirationQrCode();
            $payment->setExpiresIn($expiresIn);

            $customer = $this->createCustomer($value);
            if ($customer !== null) {
                $payment->setCustomer($customer);
            }

            $payment->setAmount($value->amount);

            $payments[] = $payment;
        }

        return $payments;
    }

    /**
     * @param $identifier
     * @return AbstractCreditCardPayment|null
     */
    private function createBaseCardPayment($data, $method)
    {
        $identifier = $data->identifier;

        try {
            $cardToken = new CardToken($identifier);
            $payment =  $this->getNewPaymentMethod($method);
            $payment->setIdentifier($cardToken);

            if (isset($data->saveOnSuccess)) {
                $payment->setSaveOnSuccess($data->saveOnSuccess);
            }
            return $payment;
        } catch(\Exception $e) {

        } catch (\Throwable $e) {

        }


        try {
            $cardId = new CardId($identifier);
            $payment =  $this->getSavedPaymentMethod($method);
            $payment->setIdentifier($cardId);

            if (isset($data->cvvCard)) {
                $payment->setCvv($data->cvvCard);
            }

            $owner = new CustomerId($data->customerId);
            $payment->setOwner($owner);

            return $payment;
        } catch(\Exception $e) {

        } catch (\Throwable $e) {

        }

        return null;
    }

    /**
     * @param $method
     * @return SavedCreditCardPayment
     */
    private function getSavedPaymentMethod($method)
    {
        $payments = [
            PaymentMethod::CREDIT_CARD => new SavedCreditCardPayment()
        ];

        if (isset($payments[$method])) {
            return $payments[$method];
        }

        throw new \Exception("payment method saved not found", 400);
    }

    /**
     * @param $method
     * @return NewCreditCardPayment
     */
    private function getNewPaymentMethod($method)
    {
        $payments = [
            PaymentMethod::CREDIT_CARD => new NewCreditCardPayment()
        ];

        if (!empty($payments[$method])) {
            return $payments[$method];
        }

        return new NewCreditCardPayment();
    }
}
