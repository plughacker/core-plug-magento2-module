<?php

namespace PlugHacker\PlugCore\Kernel\Interfaces;

interface PlatformPaymentMethodInterface
{
    public function setPaymentMethod($paymentMethod);
    public function getPaymentMethod();
}
