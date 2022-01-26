<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects\Id;

use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

class OrderId extends AbstractValidString
{
    /**
     * OrderId string constructor.
     * @param $orderId
     * @throws \PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException
     */
    public function __construct($orderId)
    {
        parent::__construct($orderId);
    }

    protected function validateValue($value)
    {
        return true;
    }
}
