<?php

namespace PlugHacker\PlugCore\Recurrence\ValueObjects;

use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

class PlanItemId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^pi_\w{16}$/', $value) === 1;
    }
}
