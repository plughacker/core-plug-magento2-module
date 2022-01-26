<?php

namespace PlugHacker\PlugCore\Hub\ValueObjects;

use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

final class HubInstallToken extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/\w{64}$/', $value) === 1;
    }
}
