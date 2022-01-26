<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects\Key;

final class TestClientId extends AbstractClientId
{
    protected function validateValue($value)
    {
        return true;
    }
}
