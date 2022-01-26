<?php

namespace PlugHacker\PlugCore\Recurrence\Services\CartRules;

interface RuleInterface
{
    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    );

    public function getError();
}
