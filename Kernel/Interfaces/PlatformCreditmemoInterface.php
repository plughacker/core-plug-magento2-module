<?php

namespace PlugHacker\PlugCore\Kernel\Interfaces;

interface PlatformCreditmemoInterface
{
    public function save();
    public function getIncrementId();
    public function prepareFor(PlatformOrderInterface $order);
    public function refund();
}
