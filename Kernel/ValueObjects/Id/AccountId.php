<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects\Id;

use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

class AccountId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^acc_\w{16}$/', $value) === 1;
    }
}
