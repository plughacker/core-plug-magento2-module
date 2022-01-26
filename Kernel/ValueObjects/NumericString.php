<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects;

class NumericString extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^\d*$/', $value) === 1;
    }
}
