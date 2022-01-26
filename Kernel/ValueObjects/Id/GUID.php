<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects\Id;

use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

class GUID extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^\w{8}-(\w{4}-){3}\w{12}$/', $value) === 1;
    }
}
