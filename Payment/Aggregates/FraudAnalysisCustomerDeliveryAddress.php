<?php
declare(strict_types=1);

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreateFraudAnalysisCustomerDeliveryAddressRequest;
use PlugHacker\PlugCore\Payment\Aggregates\FraudAnalysisCustomerBillingAddress;

final class FraudAnalysisCustomerDeliveryAddress extends FraudAnalysisCustomerBillingAddress
{
    protected function getObject()
    {
        return new CreateFraudAnalysisCustomerDeliveryAddressRequest();
    }

}
