<?php

namespace PlugHacker\PlugCore\Recurrence\Services;

use PlugHacker\PlugCore\Recurrence\Aggregates\ProductSubscription;
use PlugHacker\PlugCore\Recurrence\Aggregates\Repetition;

class RulesCheckoutService
{
    public function runRulesCheckoutSubscription(
        ProductSubscription $productSubscriptionInCart,
        ProductSubscription $productSubscriptionSelected,
        Repetition $repetitionInCart,
        Repetition $repetitionSelected
    ) {
        $repetitionCompatible = $repetitionInCart->checkRepetitionIsCompatible(
            $repetitionSelected
        );

        $productSubscriptionCompatible = $productSubscriptionInCart->checkProductHasSamePaymentMethod(
            $productSubscriptionSelected
        );

        return $repetitionCompatible && $productSubscriptionCompatible;
    }
}
