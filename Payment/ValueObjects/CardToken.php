<?php

namespace PlugHacker\PlugCore\Payment\ValueObjects;

final class CardToken extends AbstractCardIdentifier
{
    protected function validateValue($value)
    {
        return true;
    }
}
