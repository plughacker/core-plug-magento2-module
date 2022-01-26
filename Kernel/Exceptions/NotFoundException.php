<?php

namespace PlugHacker\PlugCore\Kernel\Exceptions;

class NotFoundException extends AbstractPlugCoreException
{
    public function __construct($message)
    {
        parent::__construct($message, 404, null);
    }
}
