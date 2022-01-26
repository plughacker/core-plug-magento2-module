<?php

namespace PlugHacker\PlugCore\Kernel\Exceptions;

class InvalidOperationException extends AbstractPlugCoreException
{
    public function construct($message)
    {
        parent::__construct($message, 400);
    }
}
