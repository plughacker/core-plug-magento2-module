<?php

namespace PlugHacker\PlugCore\Hub\Commands;

use Exception;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractModuleCoreSetup as  MPSetup;
use PlugHacker\PlugCore\Kernel\Aggregates\Configuration;
use PlugHacker\PlugCore\Kernel\Factories\ConfigurationFactory;
use PlugHacker\PlugCore\Kernel\Repositories\ConfigurationRepository;

class UninstallCommand extends AbstractCommand
{
    public function execute()
    {
        $moduleConfig = MPSetup::getModuleConfiguration();

        if (!$moduleConfig->isHubEnabled()) {
            throw new Exception("Hub is not installed!");
        }

        $hubKey = $moduleConfig->getSecretKey();
        if (!$hubKey->equals($this->getAccessToken())) {
            throw new Exception("Access Denied.");
        }

        $cleanConfig = json_decode(json_encode($moduleConfig));
        $cleanConfig->keys = [
            Configuration::KEY_SECRET => null,
            Configuration::KEY_CLIENT => null,
            Configuration::KEY_MERCHANT => null,
        ];
        $cleanConfig->testMode = true;
        $cleanConfig->hubInstallId = null;

        $cleanConfig = json_encode($cleanConfig);
        $configFactory = new ConfigurationFactory();
        $cleanConfig = $configFactory->createFromJsonData($cleanConfig);

        $method = $cleanConfig->getMethodsInherited();

        $methodInherited = array_merge($method, ['getSecretKey', 'getClientId', 'isHubEnabled']);

        $cleanConfig->setMethodsInherited(array_unique($methodInherited));


        $cleanConfig->setId($moduleConfig->getId());
        MPSetup::setModuleConfiguration($cleanConfig);

        $configRepo = new ConfigurationRepository();

        $configRepo->save($cleanConfig);
    }
}
