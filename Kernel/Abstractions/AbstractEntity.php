<?php

namespace PlugHacker\PlugCore\Kernel\Abstractions;

use JsonSerializable;
use PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString;

/**
 * The Entity Abstraction. All the aggregate roots that are entities should extend
 * this class.
 *
 * Holds the business rules related to entities.
 */
abstract class AbstractEntity implements JsonSerializable
{
    /**
     *
     * @var int
     */
    protected $id;

    /**
     * Almost every Entity has an equivalent at plug. This property holds the
     * Plug ID for the entity.
     *
     * @var AbstractValidString
     */
    protected $plugId;

    /**
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param  string $id
     * @return AbstractEntity
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     *
     * @return \PlugHacker\PlugCore\Kernel\ValueObjects\AbstractValidString
     */
    public function getPlugId()
    {
        return $this->plugId;
    }

    /**
     *
     * @param  AbstractValidString $plugId
     * @return AbstractEntity
     */
    public function setPlugId(AbstractValidString $plugId)
    {
        $this->plugId = $plugId;
        return $this;
    }

    /**
     * Do the identity comparison with another Entity.
     *
     * @param  AbstractEntity $entity
     * @return bool
     */
    public function equals(AbstractEntity $entity)
    {
        return $this->id === $entity->getId();
    }
}
