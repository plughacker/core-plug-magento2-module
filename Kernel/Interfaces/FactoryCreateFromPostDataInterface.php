<?php

namespace PlugHacker\PlugCore\Kernel\Interfaces;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;

interface FactoryCreateFromPostDataInterface
{
    /**
     *
     * @param  array $postData
     * @return AbstractEntity
     */
    public function createFromPostData($postData);
}
