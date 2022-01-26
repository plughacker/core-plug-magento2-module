<?php

namespace PlugHacker\PlugCore\Recurrence\Repositories;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractDatabaseDecorator;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractRepository;
use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;
use PlugHacker\PlugCore\Recurrence\Factories\RepetitionFactory;
use PlugHacker\PlugCore\Recurrence\Factories\SubProductFactory;
use PlugHacker\PlugCore\Recurrence\Interfaces\RecurrenceEntityInterface;

class SubProductRepository extends AbstractRepository
{

    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "
            INSERT INTO $table (
                `product_id`,
                `product_recurrence_id`,
                `recurrence_type`,
                `cycles`,
                `quantity`,
                `plug_id`
            ) VALUES (
                '{$object->getProductId()}',
                '{$object->getProductRecurrenceId()}',
                '{$object->getRecurrenceType()}',
                '{$object->getCycles()}',
                '{$object->getQuantity()}',
                '{$object->getPlugIdValue()}'
            )
        ";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "
            UPDATE $table SET
                `product_id` = '{$object->getProductId()}',
                `product_recurrence_id` = '{$object->getProductRecurrenceId()}',
                `recurrence_type` = '{$object->getRecurrenceType()}',
                `cycles` = '{$object->getCycles()}',
                `quantity` = '{$object->getQuantity()}'
            WHERE id = {$object->getId()}
        ";

        $this->db->query($query);
    }

    public function delete(AbstractEntity $object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "DELETE FROM $table WHERE id = {$object->getId()}";

        $this->db->query($query);
    }

    public function find($objectId)
    {
        // TODO: Implement find() method.
    }

    public function findByPlugId(AbstractValidString $plugId)
    {
        // TODO: Implement findByPlugId() method.
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }

    public function findByRecurrence($recurrenceEntity)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "SELECT * FROM $table" .
            " WHERE product_recurrence_id = {$recurrenceEntity->getId()}" .
            " AND recurrence_type = '{$recurrenceEntity->getRecurrenceType()}'";

        $result = $this->db->fetch($query);
        $subProducts = [];

        if ($result->num_rows === 0) {
            return $subProducts;
        }

        foreach ($result->rows as $row) {
            $subProductFactory = new SubProductFactory();
            $subProducts[] = $subProductFactory->createFromDbData($row);
        }

        return $subProducts;
    }

    public function findByRecurrenceIdAndProductId($recurrenceId, $productId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "SELECT * FROM $table" .
            " WHERE product_recurrence_id = {$recurrenceId}" .
            " AND product_id = '{$productId}'";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $subProductFactory = new SubProductFactory();
        return $subProductFactory->createFromDbData($result->row);
    }
}
