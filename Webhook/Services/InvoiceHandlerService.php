<?php

namespace PlugHacker\PlugCore\Webhook\Services;

use Exception;
use PlugHacker\PlugCore\Kernel\Exceptions\InvalidParamException;
use PlugHacker\PlugCore\Kernel\Exceptions\NotFoundException;
use PlugHacker\PlugCore\Kernel\Factories\ChargeFactory;
use PlugHacker\PlugCore\Kernel\Responses\ServiceResponse;
use PlugHacker\PlugCore\Kernel\Services\APIService;
use PlugHacker\PlugCore\Kernel\Services\ChargeService;
use PlugHacker\PlugCore\Webhook\Aggregates\Webhook;

class InvoiceHandlerService
{
    const COMPONENT_KERNEL = 'Kernel';
    const COMPONENT_RECURRENCE = 'Recurrence';

    /**
     * @param $component
     * @throws Exception
     */
    public function build($component)
    {
        $listInvoiceHandleService = [
            self::COMPONENT_RECURRENCE => new InvoiceRecurrenceService()
        ];

        if (empty($listInvoiceHandleService[$component])) {
            throw new Exception('Não foi encontrado o tipo de charge a ser carregado', 400);
        }

        return $listInvoiceHandleService[$component];
    }

    /**
     * @param Webhook $webhook
     * @return mixed
     * @throws InvalidParamException
     * @throws NotFoundException
     * @throws Exception
     */
    public function handle(Webhook $webhook)
    {
        $handler = $this->build($webhook->getComponent());
        return $handler->handle($webhook);
    }
}
