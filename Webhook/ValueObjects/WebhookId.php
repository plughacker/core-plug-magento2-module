<?php

namespace PlugHacker\PlugCore\Webhook\ValueObjects;

use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

class WebhookId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return true;
    }
}
