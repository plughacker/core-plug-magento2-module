<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects\Id;

use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

class InvoiceId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^in_\w{16}$/', $value) === 1;
    }
}
