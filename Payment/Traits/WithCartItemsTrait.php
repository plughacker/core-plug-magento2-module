<?php
declare(strict_types=1);

namespace PlugHacker\PlugCore\Payment\Traits;

use PlugHacker\PlugCore\Payment\Aggregates\CartItems;

trait WithCartItemsTrait
{
    protected ?CartItems $cartItems;

    public function getCartItems(): ?CartItems
    {
        return $this->cartItems;
    }

    public function setCartItems(?CartItems $cartItems)
    {
        $this->cartItems = $cartItems;
    }
}
