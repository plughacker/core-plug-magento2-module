<?php


namespace PlugHacker\PlugCore\Payment\Services\ResponseHandlers;

use PlugHacker\PlugCore\Kernel\Services\OrderLogService;
use PlugHacker\PlugCore\Payment\Interfaces\ResponseHandlerInterface;

abstract class AbstractResponseHandler implements ResponseHandlerInterface
{
    protected $logService;

    public function __construct()
    {
        $this->logService = new OrderLogService();
    }
}
