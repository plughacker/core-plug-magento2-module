<?php

namespace PlugHacker\PlugCore\Hub\Commands;

use Exception;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use PlugHacker\PlugCore\Kernel\Repositories\ConfigurationRepository;

class InstallCommand extends AbstractCommand
{
    public function execute()
    {
        $moduleConfig = MPSetup::getModuleConfiguration();

        if ($moduleConfig->isHubEnabled()) {
            throw new Exception("Hub already installed!");
        }

        $moduleConfig->setHubInstallId($this->getInstallId());

        $moduleConfig->setClientId(
            $this->getAccountClientId()
        );

        $moduleConfig->setSecretKey(
            $this->getAccessToken()
        );

        $configRepo = new ConfigurationRepository();

        $configRepo->save($moduleConfig);
    }
}
