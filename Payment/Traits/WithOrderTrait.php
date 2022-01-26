<?php

namespace PlugHacker\PlugCore\Payment\Traits;

use PlugHacker\PlugCore\Payment\Aggregates\Order;

trait WithOrderTrait
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }
}
