<?php

namespace PlugHacker\PlugCore\Kernel\Repositories;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractDatabaseDecorator;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractRepository;
use PlugHacker\PlugCore\Kernel\Aggregates\Order;
use PlugHacker\PlugCore\Kernel\Factories\OrderFactory;
use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

final class OrderRepository extends AbstractRepository
{
    /**
     *
     * @param  Order $object
     * @throws \Exception
     */
    protected function create(AbstractEntity &$object)
    {
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);

        $order = json_decode(json_encode($object));

        $query = "
          INSERT INTO $orderTable (`plug_id`, `code`, `status`)
          VALUES ('{$order->plugId}', '{$order->code}', '{$order->status}');
         ";

        $this->db->query($query);

        $chargeRepository = new ChargeRepository();
        foreach ($object->getCharges() as $charge) {
            $chargeRepository->save($charge);
            $object->updateCharge($charge, true);
        }
    }

    /**
     *
     * @param  Order $object
     * @throws \Exception
     */
    protected function update(AbstractEntity &$object)
    {
        $order = json_decode(json_encode($object));
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);

        $query = "
            UPDATE $orderTable SET
              status = '{$order->status}'
            WHERE id = {$order->id}
        ";

        $this->db->query($query);

        //update Charges;
        $chargeRepository = new ChargeRepository();
        foreach ($object->getCharges() as $charge) {
            $chargeRepository->save($charge);
            $object->updateCharge($charge, true);
        }
    }

    public function findByCode($codeId)
    {
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);

        $query = "SELECT * FROM `$orderTable` ";
        $query .= "WHERE code = '{$codeId}';";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new OrderFactory();

        return $factory->createFromDbData($result->row);
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        // TODO: Implement find() method.
    }

    /**
     * @param AbstractValidString $plugId
     * @return Order|null
     * @throws \PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException
     */
    public function findByPlugId(AbstractValidString $plugId)
    {
        $id = $plugId->getValue();
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);

        $query = "SELECT * FROM `$orderTable` ";
        $query .= "WHERE plug_id = '{$id}';";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new OrderFactory();

        return $factory->createFromDbData($result->row);
    }

    public function findByPlatformId($platformID)
    {
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);

        $query = "SELECT * FROM `$orderTable` ";
        $query .= "WHERE code = '{$platformID}';";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new OrderFactory();

        return $factory->createFromDbData($result->row);
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }
}
