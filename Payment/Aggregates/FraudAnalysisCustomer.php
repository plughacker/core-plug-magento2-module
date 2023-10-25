<?php
declare(strict_types=1);

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreateFraudAnalysisCustomerBrowserRequest;
use PlugHacker\PlugAPILib\Models\CreateFraudAnalysisCustomerRequest;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;

final class FraudAnalysisCustomer implements ConvertibleToSDKRequestsInterface
{
    private string $name;
    private string $email;
    private string $phone;
    private string $identityType;
    private string $identity;
    private string $registrationDate;
    /**
     * @var CreateFraudAnalysisCustomerBrowserRequest[]
     */
    private $browser;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getIdentityType(): string
    {
        return $this->identityType;
    }

    public function setIdentityType(string $identityType): void
    {
        $this->identityType = $identityType;
    }

    public function getIdentity(): string
    {
        return $this->identity;
    }

    public function setIdentity(string $identity): void
    {
        $this->identity = $identity;
    }

    public function getRegistrationDate(): string
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(string $registrationDate): void
    {
        $this->registrationDate = $registrationDate;
    }

    public function getBrowser()
    {
        return $this->browser;
    }

    public function setBrowser(CreateFraudAnalysisCustomerBrowserRequest $browser): void
    {
        $this->browser = $browser;
    }

    public function convertToSDKRequest()
    {
        $fraudAnalysisCustomerBrowserRequest = new CreateFraudAnalysisCustomerRequest();
        $fraudAnalysisCustomerBrowserRequest->name = $this->getName();
        $fraudAnalysisCustomerBrowserRequest->email = $this->getEmail();
        $fraudAnalysisCustomerBrowserRequest->phone = $this->getPhone();
        $fraudAnalysisCustomerBrowserRequest->identityType = $this->getIdentityType();
        $fraudAnalysisCustomerBrowserRequest->identity = $this->getIdentity();
        $fraudAnalysisCustomerBrowserRequest->registrationDate = $this->getRegistrationDate();
        $fraudAnalysisCustomerBrowserRequest->browser = $this->getBrowser();

        return $fraudAnalysisCustomerBrowserRequest;
    }
}
