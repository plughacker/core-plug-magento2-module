<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects\Id;

use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

class ChargeId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return true;
    }
}
