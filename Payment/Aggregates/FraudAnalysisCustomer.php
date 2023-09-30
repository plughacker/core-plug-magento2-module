<?php
declare(strict_types=1);

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreateFraudAnalysisCustomerBrowserRequest;
use PlugHacker\PlugAPILib\Models\CreateFraudAnalysisCustomerRequest;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;

final class FraudAnalysisCustomer implements ConvertibleToSDKRequestsInterface
{
    /**
     * @var CreateFraudAnalysisCustomerBrowserRequest[]
     */
    private $browser;

    public function getBrowser()
    {
        return $this->browser;
    }

    public function setBrowser(CreateFraudAnalysisCustomerBrowserRequest $browser): void
    {
        $this->browser = $browser;
    }

    public function convertToSDKRequest(): CreateFraudAnalysisCustomerRequest
    {
        $fraudAnalysisCustomerBrowserRequest = new CreateFraudAnalysisCustomerRequest();
        $fraudAnalysisCustomerBrowserRequest->browser = $this->getBrowser();

        return $fraudAnalysisCustomerBrowserRequest;
    }
}
