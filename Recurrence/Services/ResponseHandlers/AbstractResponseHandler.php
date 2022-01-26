<?php


namespace PlugHacker\PlugCore\Recurrence\Services\ResponseHandlers;

use PlugHacker\PlugCore\Kernel\Services\OrderLogService;

abstract class AbstractResponseHandler
{
    protected $logService;

    public function __construct()
    {
        $this->logService = new OrderLogService();
    }
}
