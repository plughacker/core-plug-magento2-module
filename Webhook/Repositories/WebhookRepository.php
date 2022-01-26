<?php

namespace PlugHacker\PlugCore\Webhook\Repositories;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractDatabaseDecorator;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractRepository;
use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;
use PlugHacker\PlugCore\Webhook\Factories\WebhookFactory;

class WebhookRepository extends AbstractRepository
{
    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_WEBHOOK);
        $query = "INSERT INTO $table (plug_id) VALUES ('{$object->getPlugId()->getValue()}')";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {

    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_WEBHOOK);
        $query = "SELECT * FROM $table WHERE id = '$objectId'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new WebhookFactory();
            $webhook = $factory->createFromDbData($result->row);

            return $webhook;
        }
        return null;
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }

    public function findByPlugId(AbstractValidString $plugId)
    {
        $id = $plugId->getValue();
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_WEBHOOK);
        $query = "SELECT * FROM $table WHERE plug_id = '$id'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new WebhookFactory();
            $webhook = $factory->createFromDbData($result->row);

            return $webhook;
        }
        return null;
    }
}
