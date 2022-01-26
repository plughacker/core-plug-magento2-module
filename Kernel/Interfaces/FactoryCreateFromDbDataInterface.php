<?php

namespace PlugHacker\PlugCore\Kernel\Interfaces;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;

interface FactoryCreateFromDbDataInterface
{
    /**
     * @param  array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData);
}
