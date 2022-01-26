<?php

namespace PlugHacker\PlugCore\Webhook\ValueObjects;

use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

class WebhookId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/hook_\w{16}$/', $value) === 1;
    }
}
