<?php

namespace PlugHacker\PlugCore\Webhook\Exceptions;

use PlugHacker\PlugCore\Kernel\Exceptions\AbstractPlugCoreException;
use PlugHacker\PlugCore\Webhook\Aggregates\Webhook;

class UnprocessableWebhookException extends AbstractPlugCoreException
{
    /**
     * UnprocessableWebhookException constructor.
     */
    public function __construct($message, $code = 422)
    {
        parent::__construct($message, $code);
    }
}
