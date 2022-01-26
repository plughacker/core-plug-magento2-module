<?php

namespace PlugHacker\PlugCore\Kernel\Exceptions;

class InvalidClassException extends AbstractPlugCoreException
{
    public function __construct($actualClass, $expectedClass)
    {
        $message = "$actualClass is not a $expectedClass!";
        parent::__construct($message, 400, null);
    }
}
