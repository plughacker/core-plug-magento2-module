<?php

namespace PlugHacker\PlugCore\Webhook\Services;

use PlugHacker\PlugCore\Kernel\Exceptions\AbstractPlugCoreException;
use PlugHacker\PlugCore\Kernel\Exceptions\NotFoundException;
use PlugHacker\PlugCore\Kernel\Services\LogService;
use PlugHacker\PlugCore\Webhook\Aggregates\Webhook;
use PlugHacker\PlugCore\Webhook\Exceptions\WebhookAlreadyHandledException;
use PlugHacker\PlugCore\Webhook\Exceptions\WebhookHandlerNotFoundException;
use PlugHacker\PlugCore\Webhook\Factories\WebhookFactory;
use PlugHacker\PlugCore\Webhook\Repositories\WebhookRepository;
use PlugHacker\PlugCore\Webhook\ValueObjects\WebhookId;

class WebhookReceiverService
{
    /**
     *
     * @param  $postData
     * @return mixed
     * @throws NotFoundException
     * @throws \PlugHacker\PlugCore\Kernel\Exceptions\InvalidClassException
     */
    public function handle($postData)
    {
        $logService = new LogService(
            'Webhook',
            true
        );
        try {
            $logService->info("Received", $postData);

            $repository = new WebhookRepository();
            $webhook = $repository->findByPlugId(new WebhookId($postData->id));
            if ($webhook !== null) {
                throw new WebhookAlreadyHandledException($webhook);
            }

            $factory = new WebhookFactory();
            $webhook = $factory->createFromPostData($postData);

            $handlerService = $this->getHandlerServiceFor($webhook);

            $return = $handlerService->handle($webhook);
            $repository->save($webhook);
            $logService->info(
                "Webhook handled successfuly",
                (object)[
                    'id' => $webhook->getId(),
                    'plugId' => $webhook->getPlugId(),
                    'result' => $return
                ]
            );

            return $return;
        } catch(AbstractPlugCoreException $e) {
            $logService->exception($e);
            throw $e;
        }
    }

    private function getHandlerServiceFor(Webhook $webhook)
    {
        $handlerServiceClass =
            'PlugHacker\\PlugCore\\Webhook\\Services\\' .
            ucfirst($webhook->getType()->getEntityType()).
            'HandlerService';

        if (!class_exists($handlerServiceClass)) {
            throw new WebhookHandlerNotFoundException($webhook);
        }

        /**
         *
         * @var AbstractHandlerService $handlerService
         */
        return new $handlerServiceClass();
    }
}
