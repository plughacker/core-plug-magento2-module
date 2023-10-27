<?php
declare(strict_types=1);

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreateFraudAnalysisCartRequest;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;

final class FraudAnalysisCart implements ConvertibleToSDKRequestsInterface
{
    /**
     * @var CreateFraudAnalysisCartRequest[]
     */
    private $items;

    public function getItems()
    {
        return $this->items;
    }

    public function setItems(CreateFraudAnalysisCartRequest $items): void
    {
        $this->items = $items;
    }

    public function convertToSDKRequest()
    {
        $fraudAnalysisCartRequest = new CreateFraudAnalysisCartRequest();
        $fraudAnalysisCartRequest->items = $this->getItems();

        return $fraudAnalysisCartRequest;
    }
}
