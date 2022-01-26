<?php

namespace PlugHacker\PlugCore\Kernel\Factories;

use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Aggregates\Configuration;
use PlugHacker\PlugCore\Kernel\Factories\Configurations\PixConfigFactory;
use PlugHacker\PlugCore\Kernel\Factories\Configurations\RecurrenceConfigFactory;
use PlugHacker\PlugCore\Kernel\Interfaces\FactoryInterface;
use PlugHacker\PlugCore\Kernel\Repositories\ConfigurationRepository;
use PlugHacker\PlugCore\Kernel\ValueObjects\CardBrand;
use PlugHacker\PlugCore\Kernel\ValueObjects\Configuration\AddressAttributes;
use PlugHacker\PlugCore\Kernel\ValueObjects\Configuration\CardConfig;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\GUID;
use PlugHacker\PlugCore\Kernel\ValueObjects\Key\HubAccessTokenKey;
use PlugHacker\PlugCore\Kernel\ValueObjects\Key\MerchantKey;
use PlugHacker\PlugCore\Kernel\ValueObjects\Key\ClientId;
use PlugHacker\PlugCore\Kernel\ValueObjects\Key\SecretKey;
use PlugHacker\PlugCore\Kernel\ValueObjects\Key\TestMerchantKey;
use PlugHacker\PlugCore\Kernel\ValueObjects\Key\TestClientId;
use PlugHacker\PlugCore\Kernel\ValueObjects\Key\TestSecretKey;
use Exception;

class ConfigurationFactory implements FactoryInterface
{
    public function createEmpty()
    {
        return new Configuration();
    }

    public function createFromPostData($postData)
    {
        $config = new Configuration();

        foreach ($postData['creditCard'] as $brand => $cardConfig) {
            $config->addCardConfig(
                new CardConfig(
                    $cardConfig['is_enabled'],
                    $brand,
                    $cardConfig['installments_up_to'],
                    $cardConfig['installments_without_interest'],
                    $cardConfig['interest'],
                    $cardConfig['incremental_interest']
                )
            );
        }

        $config->setBoletoEnabled($postData['payment_plug_boleto_status']);
        $config->setCreditCardEnabled($postData['payment_plug_credit_card_status']);

        $config->setStoreId($postData['payment_plug_store_id']);

        return $config;
    }

    public function createFromJsonData($json)
    {
        $config = new Configuration();
        $data = json_decode($json);

        $this->createCardConfigs($data, $config);

        $antifraudEnabled = false;
        $antifraudMinAmount = 0;

        if (!empty($data->antifraudEnabled)) {
            $antifraudEnabled = $data->antifraudEnabled;
            $antifraudMinAmount = $data->antifraudMinAmount;
        }

        $config->setTestMode($data->testMode);
        $config->setAntifraudEnabled($antifraudEnabled);
        $config->setAntifraudMinAmount($antifraudMinAmount);
        $config->setBoletoEnabled($data->boletoEnabled);
        $config->setCreditCardEnabled($data->creditCardEnabled);

        if (empty($data->createOrder)){
            $data->createOrder = false;
        }
        $config->setCreateOrderEnabled($data->createOrder);

        if (!empty($data->sendMail)) {
            $config->setSendMailEnabled($data->sendMail);
        }


        if (!empty($data->methodsInherited)) {
            $config->setMethodsInherited($data->methodsInherited);
        }

        if (!empty($data->inheritAll)) {
            $config->setInheritAll($data->inheritAll);
        }

        if (!empty($data->storeId) && $data->storeId !== null) {
            $config->setStoreId($data->storeId);
        }

        if (!empty($data->parentId)) {
            $configurationRepository = new ConfigurationRepository();
            $configDefault = $configurationRepository->find($data->parentId);
            $config->setParentConfiguration($configDefault);
        }

        $isInstallmentsEnabled = false;
        if (!empty($data->installmentsEnabled)) {
            $isInstallmentsEnabled = $data->installmentsEnabled;
        }
        $config->setInstallmentsEnabled($isInstallmentsEnabled);

        if (!empty($data->enabled)) {
            $config->setEnabled($data->enabled);
        }

        if (!empty($data->cardOperation)) {
            $config->setCardOperation($data->cardOperation);
        }

        if ($data->hubInstallId !== null) {
            $config->setHubInstallId(
                new GUID($data->hubInstallId)
            );
        }

        if (!empty($data->keys) ) {
            if (!isset($data->clientId)) {
                $index = Configuration::KEY_CLIENT;
                $data->clientId = $data->keys->$index;
            }

            if (!isset($data->secretKey)) {
                $index = Configuration::KEY_SECRET;
                $data->secretKey = $data->keys->$index;
            }

            if (!isset($data->merchantKey)) {
                $index = Configuration::KEY_MERCHANT;
                $data->merchantKey = $data->keys->$index;
            }
        }

        if (!empty($data->clientId)) {
            $config->setClientId(
                $this->createClientId($data->clientId)
            );
        }

        if (!empty($data->secretKey)) {
            $config->setSecretKey(
                $this->createSecretKey($data->secretKey)
            );
        }

        if (!empty($data->merchantKey)) {
            $config->setMerchantKey(
                $this->createMerchantKey($data->merchantKey)
            );
        }

        if (!empty($data->addressAttributes)) {
            $config->setAddressAttributes(
                new AddressAttributes(
                    $data->addressAttributes->street,
                    $data->addressAttributes->number,
                    $data->addressAttributes->neighborhood,
                    $data->addressAttributes->complement
                )
            );
        }

        if (!empty($data->cardStatementDescriptor)) {
            $config->setCardStatementDescriptor($data->cardStatementDescriptor);
        }

        if (!empty($data->boletoInstructions)) {
            $config->setBoletoInstructions($data->boletoInstructions);
        }

        if (!empty($data->boletoExpirationDate)) {
            $config->setBoletoExpirationDate($data->boletoExpirationDate);
        }

        if (!empty($data->boletoBankCode)) {
            $config->setBoletoBankCode($data->boletoBankCode);
        }
        if (!empty($data->boletoDueDays)) {
            $config->setBoletoDueDays((int) $data->boletoDueDays);
        }

        if (!empty($data->saveCards)) {
            $config->setSaveCards($data->saveCards);
        }

        if (!empty($data->multibuyer)) {
            $config->setMultiBuyer($data->multibuyer);
        }

        if (!empty($data->recurrenceConfig)) {
            $config->setRecurrenceConfig(
                (new RecurrenceConfigFactory())
                    ->createFromDbData($data->recurrenceConfig)
            );
        }

        if (isset($data->installmentsDefaultConfig)) {
            $config->setInstallmentsDefaultConfig(
                $data->installmentsDefaultConfig
            );
        }

        if (!empty($data->pixConfig)) {
            $config->setPixConfig(
                (new PixConfigFactory())->createFromDbData($data->pixConfig)
            );
        }

        return $config;
    }

    private function createCardConfigs($data,Configuration $config)
    {
        try {
            foreach ($data->cardConfigs as $cardConfig) {
                $brand = strtolower($cardConfig->brand);
                $config->addCardConfig(
                    new CardConfig(
                        $cardConfig->enabled,
                        CardBrand::$brand(),
                        $cardConfig->maxInstallment,
                        $cardConfig->maxInstallmentWithoutInterest,
                        $cardConfig->initialInterest,
                        $cardConfig->incrementalInterest,
                        $cardConfig->minValue
                    )
                );
            }
        } catch (Exception $e) {}
    }

    private function createClientId($key)
    {
        try {
            return new TestClientId($key);
        } catch(\Exception $e) {

        } catch(\Throwable $e) {

        }

        return new ClientId($key);
    }

    private function createMerchantKey($key)
    {
        try {
            return new TestMerchantKey($key);
        } catch(\Exception $e) {
        } catch(\Throwable $e) {
        }

        return new MerchantKey($key);
    }

    private function createSecretKey($key)
    {
        try {
            return new TestSecretKey($key);
        } catch(\Exception $e) {

        } catch(\Throwable $e) {

        }

        try {
            return new SecretKey($key);
        } catch(\Exception $e) {

        } catch(\Throwable $e) {

        }

        return new HubAccessTokenKey($key);
    }

    /**
     *
     * @param  array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData)
    {
        // TODO: Implement createFromDbData() method.
    }
}
