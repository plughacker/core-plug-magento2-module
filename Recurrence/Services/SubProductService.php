<?php

namespace PlugHacker\PlugCore\Recurrence\Services;

use PlugHacker\PlugCore\Recurrence\Repositories\SubProductRepository;

class SubProductService
{
    public function findByRecurrenceIdAndProductId($recurrenceId, $productId)
    {
        $subProductRepository = $this->getSubProductRepository();
        return $subProductRepository->findByRecurrenceIdAndProductId(
            $recurrenceId,
            $productId
        );
    }

    protected function getSubProductRepository()
    {
        return new SubProductRepository();
    }
}
