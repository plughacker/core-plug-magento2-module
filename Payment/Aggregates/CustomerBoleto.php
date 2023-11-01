<?php

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreateCustomerRequest;
use PlugHacker\PlugAPILib\Models\CreateDocumentRequest;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Services\LocalizationService;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use PlugHacker\PlugCore\Payment\ValueObjects\CustomerDocument;
use PlugHacker\PlugCore\Payment\ValueObjects\CustomerPhones;
use PlugHacker\PlugCore\Payment\ValueObjects\CustomerType;

final class CustomerBoleto extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    /** @var string */
    private $name;

    /** @var string */
    private $email;

    /** @var string */
    private $phoneNumber;

    /** @var CustomerDocument */
    private $document;

    public function __construct()
    {
        $this->i18n = new LocalizationService();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;

    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = substr($name, 0, 64);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Customer
     * @throws \Exception
     */
    public function setEmail($email)
    {
        $this->email = substr($email, 0, 64);

        if (empty($this->email)) {

            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                "email"
            );

            throw new \Exception($message, 400);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return CustomerDocument
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param CustomerDocument $document
     * @return Customer
     * @throws \Exception
     */
    public function setDocument(CustomerDocument $document)
    {
        $_document = substr($document->getNumber(), 0, 16);
        if (empty($_document)) {
            $inputName = $this->i18n->getDashboard('document');
            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                $inputName
            );

            throw new \Exception($message, 400);
        }

        $this->document = $document;

        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): mixed
    {
        $obj = new \stdClass();

        $obj->name = $this->name;
        $obj->email = $this->email;
        $obj->phoneNumber = $this->phoneNumber;
        $obj->document = $this->document;

        return $obj;
    }

    public function convertToSDKRequest()
    {
        $customerRequest = new CreateCustomerRequest();

        $customerRequest->name = $this->getName();
        $customerRequest->email = $this->getEmail();
        $customerRequest->document = $this->getDocument();
        $customerRequest->phoneNumber = $this->getPhoneNumber();

        return $customerRequest;
    }
}
