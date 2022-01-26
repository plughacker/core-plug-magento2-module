<?php

namespace PlugHacker\PlugCore\Payment\Interfaces;

use PlugHacker\PlugCore\Payment\Aggregates\Order;

interface HaveOrderInterface
{
    /**
     * @return Order
     */
    public function getOrder();

    /**
     * @param Order $order
     * @return mixed
     */
    public function setOrder(Order $order);
}
