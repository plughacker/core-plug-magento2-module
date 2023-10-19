<?php

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreateOrderRequest;
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

final class Order extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    use WithAmountTrait;
    use WithCustomerTrait;

    private $paymentMethod;

    /** @var string */
    private $orderId;

    /** @var string */
    private $statementDescriptor;

    /** @var boolean */
    private $capture;

    /** @var Item[] */
    private $items;
    /** @var null|Shipping */
    private $shipping;
    /** @var AbstractPayment[] */
    private $payments;
    /** @var boolean */
    private $closed;

    /** @var boolean */
    private $antifraudEnabled;

    public function __construct()
    {
        $this->payments = [];
        $this->items = [];
        $this->closed = true;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = substr($orderId, 0, 52);
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

    /**
     * @return boolean
     */
    public function getCapture()
    {
        return $this->capture;
    }

    /**
     * @param boolean $capture
     */
    public function setCapture($capture)
    {
        $this->capture = $capture;
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Item $item
     */
    public function addItem($item)
    {
        $this->items[] = $item;
    }

    /**
     * @return Shipping|null
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param Shipping|null $shipping
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethodName
     */
    public function setPaymentMethod($paymentMethodName)
    {
        $replace = str_replace('_', '', $paymentMethodName);
        $paymentMethodObject = $replace . 'PaymentMethod';

        $this->paymentMethod = $this->$paymentMethodObject();
    }

    /**
     * @return AbstractPayment[]
     */
    public function getPayments()
    {
        return $this->payments;
    }

    public function addPayment(AbstractPayment $payment)
    {
        $this->validatePaymentInvariants($payment);
        $this->blockOverPaymentAttempt($payment);

        $payment->setOrder($this);

        if ($payment->getCustomer() === null) {
            $payment->setCustomer($this->getCustomer());
        }

        $this->payments[] = $payment;
    }

    /**
     * @return string
     */
    public function generateIdempotencyKey()
    {
        return sha1($this->getCustomer()->getDocument()->getNumber() . $this->getOrderId());
    }

    /**
     * @return bool
     */
    public function isPaymentSumCorrect()
    {
        if (
            $this->amount === null ||
            empty($this->payments)
        ) {
            return false;
        }

        $sum = 0;
        foreach ($this->payments as $payment)
        {
            $sum += $payment->getAmount();
        }

        return $this->amount === $sum;
    }

    /**
     *  Blocks any overpayment attempt.
     *
     * @param AbstractPayment $payment
     * @throws \Exception
     */
    private function blockOverPaymentAttempt(AbstractPayment $payment)
    {
        $currentAmount = $payment->getAmount();
        foreach ($this->payments as $currentPayment) {
            $currentAmount += $currentPayment->getAmount();
        }

        if ($currentAmount > $this->amount) {
            throw new \Exception(
                'The sum of payment amounts is bigger than the amount of the order!',
                400
            );
        }
    }

    /**
     * Calls the invariant validator method of each payment method, if applicable.
     *
     * @param AbstractPayment $payment
     * @throws \Exception
     */
    private function validatePaymentInvariants(AbstractPayment $payment)
    {
        $paymentClass = $this->discoverPaymentMethod($payment);
        $paymentValidator = "validate$paymentClass";

        if (method_exists($this, $paymentValidator)) {
            $this->$paymentValidator($payment);
        }
    }

    private function discoverPaymentMethod(AbstractPayment $payment)
    {
        $paymentClass = get_class($payment);
        $paymentClass = explode ('\\', $paymentClass);
        $paymentClass = end($paymentClass);
        return $paymentClass;
    }

    private function validateSavedCreditCardPayment(SavedCreditCardPayment $payment)
    {
        if ($this->customer === null) {
            throw new \Exception(
                'To use a saved credit card payment in an order ' .
                'you must add a customer to it.',
                400
            );
        }

        $customerId = $this->customer->getPlugId();
        if ($customerId === null) {
            throw new \Exception(
                'You can\'t use a saved credit card of a fresh new customer',
                400
            );
        }

        if (!$customerId->equals($payment->getOwner())) {
            throw new \Exception(
                'The saved credit card informed doesn\'t belong to the informed customer.',
                400
            );
        }
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * @param bool $closed
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;
    }

    /**
     * @return bool
     */
    public function isAntifraudEnabled()
    {
        $antifraudMinAmount = MPSetup::getModuleConfiguration()->getAntifraudMinAmount();
        if ($this->amount < $antifraudMinAmount) {
            return false;
        }
        return $this->antifraudEnabled;
    }

    /**
     * @param bool $antifraudEnabled
     */
    public function setAntifraudEnabled($antifraudEnabled)
    {
        $this->antifraudEnabled = $antifraudEnabled;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): mixed
    {
        $obj = new \stdClass();

        $obj->customer = $this->getCustomer();
        $obj->orderId = $this->getOrderId();
        $obj->items = $this->getItems();

        $shipping = $this->getShipping();
        if ($shipping !== null) {
            $obj->shipping = $this->getShipping();
        }

        $obj->payments = $this->getPayments();
        $obj->closed = $this->isClosed();
        $obj->antifraudEnabled = $this->isAntifraudEnabled();

        return $obj;
    }

    /**
     * @return CreateOrderRequest
     */
    public function convertToSDKRequest()
    {
        $orderRequest = new CreateOrderRequest();
        $orderRequest->amount = $this->getAmount();
        $orderRequest->statementDescriptor = $this->getStatementDescriptor();
        $orderRequest->orderId = $this->getOrderId();
        $orderRequest->appInfo = $this->getAppInfo();

        $fraudAnalysis = new FraudAnalysis();

        $orderRequest->fraudAnalysis = $fraudAnalysis->convertToSDKRequest();

        // $orderRequest->customer = $this->getCustomer()->convertToSDKRequest();

        $orderRequestPayments = [];
        $paymentMethod = false;
        foreach ($this->getPayments() as $payment) {
            $orderRequestPayments = $payment->convertToSDKRequest();
            $paymentMethod = $payment::getBaseCode();
        }

        if (!empty($orderRequestPayments) && $paymentMethod) {
            if (isset($orderRequestPayments->$paymentMethod)) {
                foreach ($orderRequestPayments->$paymentMethod as $key => $value) {
                    $orderRequest->$key = $value;
                }
            }
        }

        $statementDescriptor = $orderRequest->statementDescriptor;
        if ($statementDescriptor == '' || $statementDescriptor == null) {
            $statementDescriptor = "Pedido {$orderRequest->orderId} loja";
        }

        $orderRequest->statementDescriptor = $statementDescriptor;

        return $orderRequest;
    }

    private function getAppInfo(): array
    {
        return [
            'platform' => [
                'integrator' => 'malga',
                'name' => 'magento',
                'version' => '1.0'
            ],
            'device' => [
                'name' => str_replace('"', '', (string)$_SERVER['HTTP_SEC_CH_UA_PLATFORM']),
                'version' => (string)$_SERVER['HTTP_USER_AGENT']
            ],
            'system' => [
                'name' => 'magento',
                'version' => '1.0'
            ]
        ];
    }

    private function creditcardPaymentMethod()
    {
        return PaymentMethod::credit_card();
    }

    private function boletoPaymentMethod()
    {
        return PaymentMethod::boleto();
    }

    private function pixPaymentMethod()
    {
        return PaymentMethod::pix();
    }

}
