<?php

namespace PlugHacker\PlugCore\Kernel\Services;

use Exception;
use PlugHacker\PlugAPILib\Models\CreateWebhookRequest;
use PlugHacker\PlugAPILib\PlugAPIClient;

class WebhookCreationService
{
    /**
     * @var PlugAPIClient
     */
    private $plugAPIClient;

    /**
     * @var LogService
     */
    private $logService;

    public function __construct(PlugAPIClient $plugAPIClient)
    {
        $this->plugAPIClient = $plugAPIClient;
        $this->logService = new LogService('Webhook',true);
    }

    /**
     * @param CreateWebhookRequest $webhookRequest
     * @return string|bool - json string
     * @throws Exception
     */
    public function createWebhook(CreateWebhookRequest $webhookRequest) {
        $response = null;

        $webhookController = $this->plugAPIClient->getWebooks();

        try {
            $response = $webhookController->createWebhook($webhookRequest);
        } catch (Exception $exception) {
        }

        if ($response == null) {
            throw $exception;
        }

        $this->logService->info(
            "Create webhook Response " . $webhookRequest->endpoint,
            $response
        );

        return json_decode(json_encode($response), true);
    }
}
