<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects\Key;

final class SecretKey extends AbstractSecretKey
{
    protected function validateValue($value)
    {
        return true;
    }
}
