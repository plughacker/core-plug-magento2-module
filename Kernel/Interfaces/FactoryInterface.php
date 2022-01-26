<?php

namespace PlugHacker\PlugCore\Kernel\Interfaces;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;

interface FactoryInterface extends
    FactoryCreateFromDbDataInterface,
    FactoryCreateFromPostDataInterface
{
    /**
     *
     * @param  array $postData
     * @return AbstractEntity
     */
    public function createFromPostData($postData);

    /**
     *
     * @param  array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData);
}
