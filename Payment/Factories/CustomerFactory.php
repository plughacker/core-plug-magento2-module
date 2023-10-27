<?php

namespace PlugHacker\PlugCore\Payment\Factories;

use PlugHacker\PlugCore\Payment\Aggregates\CustomerBoleto;
use PlugHacker\PlugCore\Payment\ValueObjects\CustomerDocument;
use PlugHacker\PlugCore\Kernel\Interfaces\FactoryInterface;
use PlugHacker\PlugCore\Kernel\Interfaces\PlatformCustomerInterface;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\CustomerId;
use PlugHacker\PlugCore\Payment\Aggregates\Customer;
use PlugHacker\PlugCore\Payment\ValueObjects\CustomerPhones;
use PlugHacker\PlugCore\Payment\ValueObjects\CustomerType;
use PlugHacker\PlugCore\Payment\ValueObjects\Phone;

class CustomerFactory implements FactoryInterface
{
    /**
     *
     * @param  \stdClass $postData
     * @return Customer
     */
    public function createFromPostData($postData)
    {
        $postData = json_decode(json_encode($postData));

        $customer = new Customer();

        $customer->setPlugId(
            new CustomerId($postData->id)
        );

        if (!empty($postData->code)) {
            $customer->setCode($postData->code);
        }

        return $customer;
    }

    public function createFromJson($json)
    {
        $data = json_decode($json);

        $customer = new Customer;

        $customer->setName((string)$data->name);
        $customer->setEmail((string)$data->email);
        $customer->setRegistrationDate((string)$data->registrationDate);

        $homePhone = new Phone($data->homePhone);
        $customer->setPhoneNumber((string)$homePhone->getFullNumber());

        $documentRequest = new CustomerDocument();
        $documentRequest->setDocument($data->document);
        $documentRequest->setType((string)CustomerType::individual()->getType());
        $customer->setDocument($documentRequest->convertToSDKRequest());

        $addressFactory = new AddressFactory();
        $customer->setBillingAddress($addressFactory->createFromJson($json));

        return $customer;
    }

    public function createFromJsonBoleto($json)
    {
        $data = json_decode($json);

        $customer = new CustomerBoleto;

        $customer->setName((string)$data->name);
        $customer->setEmail((string)$data->email);
        $customer->setRegistrationDate((string)$data->registrationDate);

        $homePhone = new Phone($data->homePhone);
        $customer->setPhoneNumber((string)$homePhone->getFullNumber());

        $documentRequest = new CustomerDocument();
        $documentRequest->setDocument($data->document);
        $documentRequest->setType((string)CustomerType::individual()->getType());
        $customer->setDocument($documentRequest->convertToSDKRequest());

        return $customer;
    }

    /**
     *
     * @param  array $dbData
     * @return Customer
     */
    public function createFromDbData($dbData)
    {
        $customer = new Customer;

        $customer->setPlugId(new CustomerId($dbData['plug_id']));

        return $customer;
    }

    public function createFromPlatformData(PlatformCustomerInterface $platformData)
    {
        $customer = new Customer;

        if ($platformData->getPlugId()) {
            $customer->setPlugId(
                new CustomerId($platformData->getPlugId())
            );
        }

        $customer->setName((string)$platformData->getName());
        $customer->setEmail((string)$platformData->getEmail());
        $customer->setRegistrationDate((string)$platformData->getRegistrationDate());
        $customer->setDocument($platformData->getDocument());
        /** @todo set address and phones */

        return $customer;
    }
}
