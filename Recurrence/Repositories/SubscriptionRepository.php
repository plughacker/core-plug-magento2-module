<?php

namespace PlugHacker\PlugCore\Recurrence\Repositories;

use Exception;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractDatabaseDecorator;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractRepository;
use PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException;
use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;
use PlugHacker\PlugCore\Recurrence\Aggregates\Charge;
use PlugHacker\PlugCore\Recurrence\Aggregates\Subscription;
use PlugHacker\PlugCore\Recurrence\Aggregates\SubscriptionItem;
use PlugHacker\PlugCore\Recurrence\Factories\SubProductFactory;
use PlugHacker\PlugCore\Recurrence\Factories\SubscriptionFactory;

class SubscriptionRepository extends AbstractRepository
{
    /**
     * @param AbstractValidString $plugId
     * @return AbstractEntity|Subscription|null
     * @throws InvalidParamException
     */
    public function findByPlugId(AbstractValidString $plugId)
    {
        $subscriptionTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION
        );
        $id = $plugId->getValue();

        $query = "
            SELECT *
              FROM {$subscriptionTable} as recurrence_subscription
             WHERE recurrence_subscription.plug_id = '{$id}'
        ";

        $result = $this->db->fetch($query);
        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionFactory();
        $subscription = $this->attachRelationships(
            $factory->createFromDbData($result->row)
        );

        return $subscription;
    }

    public function findByCode($code)
    {
        $subscriptionTable =
            $this->db->getTable(
                AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION
            );

        $query = "
            SELECT *
              FROM {$subscriptionTable} as recurrence_subscription
             WHERE recurrence_subscription.code = '{$code}'
        ";

        $result = $this->db->fetch($query);
        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionFactory();

        $subscription = $this->attachRelationships(
            $factory->createFromDbData($result->row)
        );

        return $subscription;
    }

    /**
     * @param Subscription|AbstractEntity $object
     * @throws Exception
     */
    protected function create(AbstractEntity &$object)
    {
        $subscriptionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION);

        $query = "
          INSERT INTO
            $subscriptionTable
            (
                customer_id,
                plug_id,
                code,
                status,
                installments,
                payment_method,
                recurrence_type,
                interval_type,
                interval_count,
                plan_id
            )
          VALUES
        ";

        $query .= "
            (
                '{$object->getCustomer()->getPlugId()->getValue()}',
                '{$object->getPlugId()->getValue()}',
                '{$object->getCode()}',
                '{$object->getStatus()->getStatus()}',
                '{$object->getInstallments()}',
                '{$object->getPaymentMethod()}',
                '{$object->getRecurrenceType()}',
                '{$object->getIntervalType()}',
                '{$object->getIntervalCount()}',
                '{$object->getPlanIdValue()}'
            );
        ";

        $this->db->query($query);

        if (!empty($object->getItems())) {
            $this->saveSubscriptionItem($object->getItems());
        }
    }

    protected function saveSubscriptionItem($items)
    {
        foreach ($items as $item) {
            $subscriptionItemsRepository = new SubscriptionItemRepository();
            $subscriptionItemsRepository->save($item);
        }
    }

    /**
     * @param Subscription|AbstractEntity $object
     * @throws Exception
     */
    protected function update(AbstractEntity &$object)
    {
        $subscriptionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION);

        $query = "
            UPDATE {$subscriptionTable} SET
              plug_id = '{$object->getPlugId()->getValue()}',
              code = '{$object->getCode()}',
              status = '{$object->getStatus()->getStatus()}',
              installments = '{$object->getInstallments()}',
              payment_method = '{$object->getPaymentMethod()}',
              recurrence_type = '{$object->getRecurrenceType()}',
              interval_type = '{$object->getIntervalType()}',
              interval_count = '{$object->getIntervalCount()}'
            WHERE id = {$object->getId()}
        ";

        $this->db->query($query);
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param $objectId
     * @return AbstractEntity|Subscription|null
     * @throws InvalidParamException
     */
    public function find($objectId)
    {
        $table =
            $this->db->getTable(
                AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION
            );

        $query = "SELECT * FROM $table WHERE id = '" . $objectId . "'";
        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionFactory();
        $subscription = $this->attachRelationships(
            $factory->createFromDbData($result->row)
        );

        return $subscription;
    }

    /**
     * @param $limit
     * @param $listDisabled
     * @return Subscription[]|array
     * @throws InvalidParamException
     */
    public function listEntities($limit, $listDisabled)
    {
        $table =
            $this->db->getTable(
                AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION
            );

        $query = "SELECT * FROM `{$table}` as t";

        if ($limit !== 0) {
            $limit = intval($limit);
            $query .= " LIMIT $limit";
        }

        $result = $this->db->fetch($query . ";");

        $factory = new SubscriptionFactory();

        $listSubscription = [];
        foreach ($result->rows as $row) {
            $subscription = $this->attachRelationships(
                $factory->createFromDbData($row)
            );

            $listSubscription[] = $subscription;
        }

        return $listSubscription;
    }

    /**
     * @param $customerId
     * @return AbstractEntity|Subscription[]|null
     * @throws InvalidParamException
     */
    public function findByCustomerId($customerId)
    {
        $recurrenceTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION)
        ;

        $customerTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_CUSTOMER)
        ;

        $query = "
            SELECT recurrence_subscription.*
              FROM {$recurrenceTable} as recurrence_subscription
              JOIN {$customerTable} as customer ON (recurrence_subscription.customer_id = customer.plug_id)
             WHERE customer.code = '{$customerId}'
        ";

        $result = $this->db->fetch($query);
        if ($result->num_rows === 0) {
            return [];
        }

        $factory = new SubscriptionFactory();

        $listSubscription = [];
        foreach ($result->rows as $row) {
            $subscription = $this->attachRelationships(
                $factory->createFromDbData($row)
            );
            $listSubscription[] = $subscription;
        }

        return $listSubscription;
    }

    protected function attachRelationships(Subscription $subscription)
    {
        if (!$subscription) {
            return null;
        }

        $chargeFactory = new ChargeRepository();
        $charges = $chargeFactory->findBySubscriptionId($subscription->getPlugId());
        foreach ($charges as $charge) {
            $subscription->addCharge($charge);
        }

        $subscriptionItemFactory = new SubscriptionItemRepository();
        $subscriptionItems = $subscriptionItemFactory->findBySubscriptionId($subscription->getPlugId());

        if ($subscriptionItems === null) {
            return $subscription;
        }

        foreach ($subscriptionItems as $subscriptionItem) {
            $subscription->addItem($subscriptionItem);
        }

        return $subscription;
    }
}
