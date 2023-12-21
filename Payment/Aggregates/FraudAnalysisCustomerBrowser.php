<?php
declare(strict_types=1);

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreateFraudAnalysisCustomerBrowserRequest;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;

final class FraudAnalysisCustomerBrowser implements ConvertibleToSDKRequestsInterface
{
    private string $browserFingerprint;
    private string $email;
    private string $hostName;
    private string $ipAddress;
    private string $type;

    public function getBrowserFingerprint(): string
    {
        return $this->browserFingerprint;
    }

    public function setBrowserFingerprint(string $browserFingerprint): void
    {
        $this->browserFingerprint = $browserFingerprint;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getHostName(): string
    {
        return $this->hostName;
    }

    public function setHostName(string $hostName): void
    {
        $this->hostName = $hostName;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function convertToSDKRequest()
    {
        $fraudAnalysisCustomerBrowserRequest = new CreateFraudAnalysisCustomerBrowserRequest();
        $fraudAnalysisCustomerBrowserRequest->browserFingerprint = $this->getBrowserFingerprint();
        $fraudAnalysisCustomerBrowserRequest->email = $this->getEmail();
        $fraudAnalysisCustomerBrowserRequest->hostName = $this->getHostName();
        $fraudAnalysisCustomerBrowserRequest->ipAddress = $this->getIpAddress();
        $fraudAnalysisCustomerBrowserRequest->type = $this->getType();

        return $fraudAnalysisCustomerBrowserRequest;
    }
}
