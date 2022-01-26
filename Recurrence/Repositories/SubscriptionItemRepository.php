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
use PlugHacker\PlugCore\Recurrence\Factories\SubscriptionItemFactory;

class SubscriptionItemRepository extends AbstractRepository
{
    /**
     * @param AbstractValidString $plugId
     * @return AbstractEntity|Subscription|null
     * @throws InvalidParamException
     */
    public function findByPlugId(AbstractValidString $plugId)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );
        $id = $plugId->getValue();

        $query = "
            SELECT *
              FROM {$subscriptionItemTable}
             WHERE plug_id = '{$id}'
        ";

        $result = $this->db->fetch($query);
        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionItemFactory();
        $subscriptionItem = $factory->createFromDbData($result->row);

        return $subscriptionItem;
    }

    public function findBySubscriptionId(AbstractValidString $plugId)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );
        $id = $plugId->getValue();

        $query = "
            SELECT *
              FROM {$subscriptionItemTable}
             WHERE subscription_id = '{$id}'
        ";

        $result = $this->db->fetch($query);
        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionItemFactory();

        $listSubscriptionItem = [];
        foreach ($result->rows as $row) {
            $subscriptionItem = $factory->createFromDbData($row);
            $listSubscriptionItem[] = $subscriptionItem;
        }

        return $listSubscriptionItem;
    }

    public function findByCode($code)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );

        $query = "
            SELECT *
              FROM {$subscriptionItemTable}
             WHERE code = '{$code}'
        ";

        $result = $this->db->fetch($query);
        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionItemFactory();

        $subscriptionItem = $factory->createFromDbData($result->row);

        return $subscriptionItem;
    }

    /**
     * @param Subscription|AbstractEntity $object
     * @throws Exception
     */
    protected function create(AbstractEntity &$object)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );

        $query = "
          INSERT INTO
            $subscriptionItemTable
            (
                plug_id,
                subscription_id,
                code,
                quantity
            )
          VALUES
        ";

        $query .= "
            (
                '{$object->getPlugId()->getValue()}',
                '{$object->getSubscriptionId()->getValue()}',
                '{$object->getCode()}',
                '{$object->getQuantity()}'
            );
        ";

        $this->db->query($query);
    }

    /**
     * @param Subscription|AbstractEntity $object
     * @throws Exception
     */
    protected function update(AbstractEntity &$object)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );

        $query = "
            UPDATE {$subscriptionItemTable} SET
              plug_id = '{$object->getPlugId()->getValue()}',
              code = '{$object->getCode()}',
              subscription_id = '{$object->getSubscriptionId()->getValue()}',
              quantity = '{$object->getQuantity()}'
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
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );

        $query = "SELECT * FROM {$subscriptionItemTable} WHERE id = '" . $objectId . "'";
        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionItemFactory();

        $subscriptionItem = $factory->createFromDbData($result->row);

        return $subscriptionItem;
    }

    /**
     * @param $limit
     * @param $listDisabled
     * @return Subscription[]|array
     * @throws InvalidParamException
     */
    public function listEntities($limit, $listDisabled)
    {
        //@TODO Implement listEntities method
    }
}
