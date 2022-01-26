<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects\Id;

use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

class SubscriptionId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^sub_\w{16}$/', $value) === 1;
    }
}
