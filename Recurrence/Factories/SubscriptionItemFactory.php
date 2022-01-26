<?php

namespace PlugHacker\PlugCore\Recurrence\Factories;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException;
use PlugHacker\PlugCore\Kernel\Interfaces\FactoryInterface;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\SubscriptionId;
use PlugHacker\PlugCore\Recurrence\Aggregates\SubscriptionItem;
use PlugHacker\PlugCore\Recurrence\ValueObjects\SubscriptionItemId;

class SubscriptionItemFactory implements FactoryInterface
{
    /**
     * @param array $postData
     * @return AbstractEntity|Subscription
     * @throws InvalidParamException
     */
    public function createFromPostData($postData)
    {
        $subscriptionItem = new SubscriptionItem();

        $subscriptionItem->setSubscriptionId(new SubscriptionId($postData['subscription_id']));
        $subscriptionItem->setPlugId(new SubscriptionItemId($postData['id']));
        $subscriptionItem->setCode($postData['code']);
        $subscriptionItem->setQuantity($postData['quantity']);

        return $subscriptionItem;
    }
    /**
     * @param array $dbData
     * @return AbstractEntity|Subscription
     * @throws InvalidParamException
     */
    public function createFromDbData($dbData)
    {
        $subscriptionItem = new SubscriptionItem();

        $subscriptionItem->setId($dbData["id"]);
        $subscriptionItem->setSubscriptionId(new SubscriptionId($dbData['subscription_id']));
        $subscriptionItem->setPlugId(new SubscriptionItemId($dbData['plug_id']));
        $subscriptionItem->setCode($dbData['code']);
        $subscriptionItem->setQuantity($dbData['quantity']);

        return $subscriptionItem;
    }
}
