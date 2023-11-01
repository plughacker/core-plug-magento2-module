<?php
declare(strict_types=1);

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreateFraudAnalysisCustomerBillingAddressRequest;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;

class FraudAnalysisCustomerBillingAddress implements ConvertibleToSDKRequestsInterface
{
    public string $country;
    public string $state;
    public string $city;
    public string $district;
    public string $zipCode;
    public string $street;
    public string $number;
    public string $complement;

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getDistrict(): string
    {
        return $this->district;
    }

    public function setDistrict(string $district): void
    {
        $this->district = $district;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    public function getComplement(): string
    {
        return $this->complement;
    }

    public function setComplement(string $complement): void
    {
        $this->complement = $complement;
    }

    public function convertToSDKRequest()
    {
        $object = $this->getObject();
        $object->country = $this->getCountry();
        $object->state = $this->getState();
        $object->city = $this->getCity();
        $object->district = $this->getDistrict();
        $object->zipCode = $this->getZipCode();
        $object->street = $this->getStreet();
        $object->number = $this->getNumber();
        $object->complement = $this->getComplement();

        return $object;
    }

    protected function getObject()
    {
        return new CreateFraudAnalysisCustomerBillingAddressRequest();
    }
}
