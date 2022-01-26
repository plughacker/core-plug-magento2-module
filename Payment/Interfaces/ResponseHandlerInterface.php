<?php

namespace PlugHacker\PlugCore\Payment\Interfaces;

use PlugHacker\PlugCore\Payment\Aggregates\Order as PaymentOrder;

interface ResponseHandlerInterface
{
    public function handle($response, PaymentOrder $paymentOrder = null);
}
