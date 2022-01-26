<?php

namespace PlugHacker\PlugCore\Payment\Repositories;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractDatabaseDecorator;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractRepository;
use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;
use PlugHacker\PlugCore\Payment\Aggregates\Customer;
use PlugHacker\PlugCore\Payment\Factories\CustomerFactory;


final class CustomerRepository extends AbstractRepository
{
    public function findByCode($customerCode)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CUSTOMER);
        $query = "SELECT * FROM $table WHERE code = '$customerCode'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new CustomerFactory();
            $customer = $factory->createFromDbData($result->row);

            return $customer;
        }
        return null;
    }

    /** @param Customer $object */
    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CUSTOMER);

        $obj = json_decode(json_encode($object));

        $query = "
          INSERT INTO $table
            (
                code,
                plug_id
            )
          VALUES
            (
                '{$obj->code}',
                '{$obj->plugId}'
            )
        ";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        // TODO: Implement update() method.
    }

    public function deleteByCode($customerCode)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CUSTOMER);
        $query = "DELETE FROM $table WHERE code = '$customerCode'";

        return $this->db->query($query);
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        // TODO: Implement find() method.
    }

    public function findByPlugId(AbstractValidString $plugId)
    {
        $id = $plugId->getValue();
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CUSTOMER);
        $query = "SELECT * FROM $table WHERE plug_id = '$id'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new CustomerFactory();
            $customer = $factory->createFromDbData(end($result->rows));

            return $customer;
        }
        return null;
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }
}
