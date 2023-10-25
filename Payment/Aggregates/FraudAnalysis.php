<?php
declare(strict_types=1);

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreateFraudAnalysisCustomerRequest;
use PlugHacker\PlugAPILib\Models\CreateFraudAnalysisRequest;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;

final class FraudAnalysis implements ConvertibleToSDKRequestsInterface
{
    /**
     * @var CreateFraudAnalysisCustomerRequest[]
     */
    private $customer;

    public function getCustomer()
    {
        return $this->customer;
    }

    public function setCustomer(CreateFraudAnalysisCustomerRequest $customer): void
    {
        $this->customer = $customer;
    }

    public function convertToSDKRequest()
    {
        $fraudAnalysisRequest = new CreateFraudAnalysisRequest();
        $fraudAnalysisRequest->customer = $this->getCustomer();

        return $fraudAnalysisRequest;
    }
}
