<?php

namespace PlugHacker\PlugCore\Kernel\Services;

use Exception;
use PlugHacker\PlugAPILib\APIException;
use PlugHacker\PlugAPILib\Configuration;
use PlugHacker\PlugAPILib\Controllers\ChargesController;
use PlugHacker\PlugAPILib\Controllers\CustomersController;
use PlugHacker\PlugAPILib\Controllers\OrdersController;
use PlugHacker\PlugAPILib\Exceptions\ErrorException;
use PlugHacker\PlugAPILib\Models\CreateCancelChargeRequest;
use PlugHacker\PlugAPILib\Models\CreateCaptureChargeRequest;
use PlugHacker\PlugAPILib\Models\CreateOrderRequest;
use PlugHacker\PlugAPILib\PlugAPIClient;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use PlugHacker\PlugCore\Kernel\Aggregates\Charge;
use PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException;
use PlugHacker\PlugCore\Kernel\Exceptions\NotFoundException;
use PlugHacker\PlugCore\Kernel\Factories\OrderFactory;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\ChargeId;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\OrderId;
use PlugHacker\PlugCore\Maintenance\Services\ConfigInfoRetrieverService;
use PlugHacker\PlugCore\Payment\Aggregates\Customer;
use PlugHacker\PlugCore\Payment\Aggregates\Order;
use PlugHacker\PlugCore\Payment\Aggregates\Webhook;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\SubscriptionId;
use PlugHacker\PlugCore\Recurrence\Aggregates\Invoice;
use PlugHacker\PlugCore\Recurrence\Aggregates\Subscription;
use PlugHacker\PlugCore\Recurrence\Factories\SubscriptionFactory;

class APIService
{
    /**
     * @var PlugAPIClient
     */
    private $apiClient;

    /**
     * @var OrderLogService
     */
    private $logService;

    /**
     * @var ConfigInfoRetrieverService
     */
    private $configInfoService;

    /**
     * @var OrderCreationService
     */
    private $orderCreationService;

    /**
     * @var WebhookCreationService
     */
    private $webhookCreationService;

    public function __construct()
    {
        $this->apiClient = $this->getPlugApiClient();
        $this->logService = new OrderLogService(2);
        $this->configInfoService = new ConfigInfoRetrieverService();
        $this->orderCreationService = new OrderCreationService($this->apiClient);
        $this->webhookCreationService = new WebhookCreationService($this->apiClient);
    }

    public function getCharge(ChargeId $chargeId)
    {
        try {
            $chargeController = $this->getChargeController();

            $this->logService->orderInfo(
                $chargeId,
                'Get charge from api'
            );

            $response = $chargeController->getCharge($chargeId->getValue());

            $this->logService->orderInfo(
                $chargeId,
                'Get charge response: ',
                $response
            );

            return json_decode(json_encode($response), true);
        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    public function cancelCharge(Charge &$charge, $amount = 0)
    {
        try {
            $chargeId = $charge->getPlugId()->getValue();
            $request = new CreateCancelChargeRequest();
            $request->amount = $amount;

            if (empty($amount)) {
                $request->amount = $charge->getAmount();
            }

            $chargeController = $this->getChargeController();
            $chargeController->cancelCharge($chargeId, $request);
            $charge->cancel($amount);

            return null;
        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    public function captureCharge(Charge &$charge, $amount = 0)
    {
        try {
            $chargeId = $charge->getPlugId()->getValue();
            $request = new CreateCaptureChargeRequest();
            $request->amount = $amount;

            $chargeController = $this->getChargeController();
            return $chargeController->captureCharge($chargeId, $request);
        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param Order $order
     * @return array|mixed
     * @throws Exception
     */
    public function createOrder(Order $order)
    {
        $endpoint = $this->getAPIBaseEndpoint();

        $orderRequest = $order->convertToSDKRequest();
        $orderRequest->metadata = $this->getRequestMetaData();
        $orderRequest->merchantId = MPSetup::getModuleConfiguration()->getMerchantKey()->getValue();;

        $clientId = MPSetup::getModuleConfiguration()->getClientId()->getValue();
        $configInfo = $this->configInfoService->retrieveInfo("");

        $this->logService->orderInfo(
            $order->getOrderId(),
            "Snapshot config from {$clientId}",
            $configInfo
        );

        $message =
            'Create order Request from ' .
            $clientId .
            ' to ' .
            $endpoint;

        $this->logService->orderInfo(
            $order->getOrderId(),
            $message,
            $orderRequest
        );

        try {
            return $this->orderCreationService->createOrder(
                $orderRequest,
                $order->generateIdempotencyKey(),
                3
            );
        } catch (ErrorException $e) {
            $this->logService->exception($e);
            return ["message" => $e->getMessage()];
        }
    }

    /**
     * @param Webhook $order
     * @return array|mixed
     * @throws Exception
     */
    public function createWebhook(Webhook $webhook)
    {
        $endpoint = $this->getAPIBaseEndpoint();

        $webhookRequest = $webhook->convertToSDKRequest();
        $clientId = MPSetup::getModuleConfiguration()->getClientId()->getValue();
        $message = 'Create webhook Request from ' . $clientId . ' to ' . $endpoint;
        $this->logService->orderInfo(
            $clientId,
            $message,
            $webhookRequest
        );

        try {
            return $this->webhookCreationService->createWebhook($webhookRequest);
        } catch (ErrorException $e) {
            $this->logService->exception($e);
            return ["message" => $e->getMessage()];
        }
    }

    private function getRequestMetaData()
    {
        $versionService = new VersionService();
        $metadata = new \stdClass();

        $metadata->moduleVersion = $versionService->getModuleVersion();
        $metadata->coreVersion = $versionService->getCoreVersion();
        $metadata->platformVersion = $versionService->getPlatformVersion();

        return $metadata;
    }

    /**
     * @param OrderId $orderId
     * @return AbstractEntity|\PlugHacker\PlugCore\Kernel\Aggregates\Order|string
     * @throws InvalidParamException
     * @throws NotFoundException
     */
    public function getOrder(OrderId $orderId)
    {
        try {
            $orderController = $this->getOrderController();
            $orderData = $orderController->getOrder($orderId->getValue());

            $orderData = json_decode(json_encode($orderData), true);

            $orderFactory = new OrderFactory();

            return $orderFactory->createFromPostData($orderData);
        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return ChargesController
     */
    private function getChargeController()
    {
        return $this->apiClient->getCharges();
    }

    /**
     * @return OrdersController
     */
    private function getOrderController()
    {
        return $this->apiClient->getOrders();
    }

    /**
     * @return CustomersController
     */
    private function getCustomerController()
    {
        return $this->apiClient->getCustomers();
    }

    private function getPlugApiClient()
    {
        $config = MPSetup::getModuleConfiguration();
        $clientId = null;
        if ($config->getClientId() != null) {
            $clientId = $config->getClientId()->getValue();
        }

        $secretKey = null;
        if ($config->getSecretKey() != null) {
            $secretKey = $config->getSecretKey()->getValue();
        }

        return new PlugAPIClient($clientId, $secretKey);
    }

    private function getAPIBaseEndpoint()
    {
        $config = MPSetup::getModuleConfiguration();
        if ($config->isTestMode()) {
            return Configuration::$TEST_BASEURI;
        }
        return Configuration::$BASEURI;
    }

    public function updateCustomer(Customer $customer)
    {
        return $this->getCustomerController()->updateCustomer(
            $customer->getPlugId()->getValue(),
            $customer->convertToSDKRequest()
        );
    }

    private function getSubscriptionController()
    {
        return $this->apiClient->getSubscriptions();
    }

    private function getInvoiceController()
    {
        return $this->apiClient->getInvoices();
    }

    /**
     * @param Subscription $subscription
     * @return mixed|null
     * @throws APIException
     */
    public function createSubscription(Subscription $subscription)
    {
        $endpoint = $this->getAPIBaseEndpoint();

        $subscription->addMetaData(
            json_decode(json_encode($this->getRequestMetaData()), true)
        );

        $subscriptionRequest = $subscription->convertToSDKRequest();
        $clientId = MPSetup::getModuleConfiguration()->getClientId()->getValue();

        $message =
            'Create subscription request from ' .
            $clientId .
            ' to ' .
            $endpoint;

        $this->logService->orderInfo(
            $subscription->getCode(),
            $message,
            $subscriptionRequest
        );

        $subscriptionController = $this->getSubscriptionController();

        try {
            $response = $subscriptionController->createSubscription($subscriptionRequest);
            $this->logService->orderInfo(
                $subscription->getCode(),
                'Create subscription response',
                $response
            );

            return json_decode(json_encode($response), true);
        } catch (ErrorException $e) {
            $this->logService->exception($e);
            return null;
        }
    }

    /**
     * @param SubscriptionId $subscriptionId
     * @return AbstractEntity|Subscription|string
     * @throws InvalidParamException
     */
    public function getSubscription(SubscriptionId $subscriptionId)
    {
        try {
            $subscriptionController = $this->getSubscriptionController();

            $subscriptionData = $subscriptionController->getSubscription(
                $subscriptionId->getValue()
            );

            $subscriptionData = json_decode(json_encode($subscriptionData), true);

            $subscriptionData['interval_type'] = $subscriptionData['interval'];

            $subscriptionFactory = new SubscriptionFactory();
            return $subscriptionFactory->createFromPostData($subscriptionData);
        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param SubscriptionId $subscriptionId
     * @return mixed|string
     */
    public function getSubscriptionInvoice(SubscriptionId $subscriptionId)
    {
        try {
            $invoiceController = $this->getInvoiceController();

            $this->logService->orderInfo(
                $subscriptionId,
                'Get invoice from subscription.'
            );

            $response = $invoiceController->getInvoices(
                1,
                1,
                null,
                null,
                $subscriptionId->getValue()
            );

            $this->logService->orderInfo(
                $subscriptionId,
                'Invoice response: ',
                $response
            );

            return json_decode(json_encode($response), true);
        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    public function cancelSubscription(Subscription $subscription)
    {
        $endpoint = $this->getAPIBaseEndpoint();

        $clientId = MPSetup::getModuleConfiguration()->getClientId()->getValue();

        $message =
            'Cancel subscription request from ' .
            $clientId .
            ' to ' .
            $endpoint;

        $this->logService->orderInfo(
            $subscription->getCode(),
            $message
        );

        $subscriptionController = $this->getSubscriptionController();

        try {
            $response = $subscriptionController->cancelSubscription(
                $subscription->getPlugId()
            );
            $this->logService->orderInfo(
                $subscription->getCode(),
                'Cancel subscription response',
                $response
            );

            return json_decode(json_encode($response), true);
        } catch (Exception $e) {
            $this->logService->exception($e);
            return $e;
        }
    }

    /**
     * @param Invoice $invoice
     * @param int $amount
     * @return mixed
     * @throws APIException
     */
    public function cancelInvoice(Invoice &$invoice, $amount = 0)
    {
        try {
            $invoiceId = $invoice->getPlugId()->getValue();
            $invoiceController = $this->apiClient->getInvoices();

            return $invoiceController->cancelInvoice($invoiceId);
        } catch (APIException $e) {
            throw $e;
        }
    }
}
