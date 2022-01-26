<?php

namespace PlugHacker\PlugCore\Recurrence\ValueObjects;

use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

class InvoiceIdValueObject extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/in_\w{16}$/', $value) === 1;
    }
}
