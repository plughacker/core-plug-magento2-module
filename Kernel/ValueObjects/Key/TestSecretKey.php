<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects\Key;

final class TestSecretKey extends AbstractSecretKey
{
    protected function validateValue($value)
    {
        return true;
    }
}
