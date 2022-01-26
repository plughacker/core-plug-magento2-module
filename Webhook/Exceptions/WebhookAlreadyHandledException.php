<?php

namespace PlugHacker\PlugCore\Webhook\Exceptions;

use PlugHacker\PlugCore\Kernel\Exceptions\AbstractPlugCoreException;
use PlugHacker\PlugCore\Webhook\Aggregates\Webhook;

class WebhookAlreadyHandledException extends AbstractPlugCoreException
{
    /**
     * WebhookHandlerNotFound constructor.
     */
    public function __construct(Webhook $webhook)
    {
        $message = "Webhoook {$webhook->getPlugId()->getValue()} already handled!";
        parent::__construct($message, 200);
    }
}
