<?php

namespace PlugHacker\PlugCore\Recurrence\ValueObjects;

use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

class PlanId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^plan_\w{16}$/', $value) === 1;
    }
}
