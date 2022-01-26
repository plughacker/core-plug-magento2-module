<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects\Id;

use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

class CycleId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^cycle_\w{16}$/', $value) === 1;
    }
}
