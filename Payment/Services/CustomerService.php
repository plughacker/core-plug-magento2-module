<?php

namespace PlugHacker\PlugCore\Payment\Services;

use PlugHacker\PlugCore\Kernel\Interfaces\PlatformCustomerInterface;
use PlugHacker\PlugCore\Kernel\Services\APIService;
use PlugHacker\PlugCore\Kernel\Services\LogService;
use PlugHacker\PlugCore\Payment\Aggregates\Customer;
use PlugHacker\PlugCore\Payment\Factories\CustomerFactory;
use PlugHacker\PlugCore\Payment\Repositories\CustomerRepository;

class CustomerService
{
    /** @var LogService  */
    protected $logService;

    public function __construct()
    {
        $this->logService = new LogService(
            'CustomerService',
            true
        );
    }

    public function updateCustomerAtPlug(PlatformCustomerInterface $platformCustomer)
    {
        $customerFactory = new CustomerFactory();
        $customer = $customerFactory->createFromPlatformData($platformCustomer);

        if ($customer->getPlugId() !== null) {
            $this->logService->info("Update customer at Plug: [{$customer->getPlugId()}]");
            $this->logService->info("Customer request", $customer);
            $apiService = new ApiService();
            $apiService->updateCustomer($customer);
        }
    }

    public function deleteCustomerOnPlatform(PlatformCustomerInterface $platformCustomer)
    {
        $customerFactory = new CustomerFactory();
        $customer = $customerFactory->createFromPlatformData($platformCustomer);

        $customerRepository = new CustomerRepository();
        $customerRepository->deleteByCode($customer->getCode());
    }

    /**
     * @param Customer $customer
     */
    public function saveCustomer(Customer $customer)
    {

        if (empty($customer) || $customer->getCode() === null) {
            return;
        }

        $customerRepository = new CustomerRepository();

        if ($customerRepository->findByCode($customer->getCode()) !== null) {
            $customerRepository->deleteByCode($customer->getCode());
        }

        if (
            $customerRepository->findByPlugId($customer->getPlugId()) === null
        ) {
            $customerRepository->save($customer);
        }
    }
}
