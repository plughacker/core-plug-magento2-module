<?php

namespace PlugHacker\PlugCore\Recurrence\ValueObjects;

use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

class SubscriptionItemId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^si_\w{16}$/', $value) === 1;
    }
}
