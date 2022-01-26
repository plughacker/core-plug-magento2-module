<?php

namespace PlugHacker\PlugCore\Kernel\Interfaces;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;

interface SensibleDataInterface
{
    /**
     *
     * @param  string
     * @return string
     */
    public function hideSensibleData($string);
}
