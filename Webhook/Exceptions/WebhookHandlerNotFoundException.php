<?php

namespace PlugHacker\PlugCore\Webhook\Exceptions;

use PlugHacker\PlugCore\Kernel\Exceptions\AbstractPlugCoreException;
use PlugHacker\PlugCore\Webhook\Aggregates\Webhook;

class WebhookHandlerNotFoundException extends AbstractPlugCoreException
{
    /**
     * WebhookHandlerNotFound constructor.
     */
    public function __construct(Webhook $webhook)
    {
        $message =
            "Handler for {$webhook->getType()->getEntityType()}." .
            "{$webhook->getType()->getAction()} webhook not found!";
        parent::__construct($message, 200, null);
    }
}
