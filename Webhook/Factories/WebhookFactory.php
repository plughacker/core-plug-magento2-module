<?php

namespace PlugHacker\PlugCore\Webhook\Factories;

use PlugHacker\PlugCore\Kernel\Exceptions\InvalidClassException;
use PlugHacker\PlugCore\Kernel\Interfaces\FactoryInterface;
use PlugHacker\PlugCore\Kernel\Services\FactoryService;
use PlugHacker\PlugCore\Webhook\Aggregates\Webhook;
use PlugHacker\PlugCore\Webhook\Exceptions\WebhookHandlerNotFoundException;
use PlugHacker\PlugCore\Webhook\ValueObjects\WebhookId;
use PlugHacker\PlugCore\Webhook\ValueObjects\WebhookType;

class WebhookFactory implements FactoryInterface
{
    /**
     *
     * @param  $postData
     * @return Webhook
     * @throws \PlugHacker\PlugCore\Kernel\Exceptions\InvalidClassException
     */
    public function createFromPostData($postData)
    {
        $webhook = new Webhook();

        $webhook->setPlugId(new WebhookId($postData->id));
        $webhook->setType(WebhookType::fromPostType($postData->type));
        $webhook->setComponent($postData->data);

        $factoryService = new FactoryService;

        try {
            $entityFactory =
                $factoryService->getFactoryFor(
                    $webhook->getComponent(),
                    $webhook->getType()->getEntityType()
                );
        }catch(InvalidClassException $e) {
            throw new WebhookHandlerNotFoundException($webhook);
        }

        $entity = $entityFactory->createFromPostData($postData->data);

        $webhook->setEntity($entity);

        return $webhook;
    }

    /**
     *
     * @param  $dbData
     * @return Webhook
     */
    public function createFromDbData($dbData)
    {
        $webhook = new Webhook();

        $webhook->setId($dbData['id']);
        $webhook->setPlugId(new WebhookId($dbData['plug_id']));

        return $webhook;
    }
}
