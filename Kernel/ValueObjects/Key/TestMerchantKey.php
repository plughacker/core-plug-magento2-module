<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects\Key;

final class TestMerchantKey extends AbstractMerchantKey
{
    protected function validateValue($value)
    {
        return true;
    }
}
