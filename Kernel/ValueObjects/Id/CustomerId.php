<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects\Id;

use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

class CustomerId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^cus_\w{16}$/', $value) === 1;
    }
}
