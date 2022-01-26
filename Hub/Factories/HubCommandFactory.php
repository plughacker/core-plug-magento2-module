<?php

namespace PlugHacker\PlugCore\Hub\Factories;

use PlugHacker\PlugCore\Hub\Commands\AbstractCommand;
use PlugHacker\PlugCore\Hub\Commands\CommandType;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\AccountId;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\GUID;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\MerchantId;
use PlugHacker\PlugCore\Kernel\ValueObjects\Key\HubAccessTokenKey;
use PlugHacker\PlugCore\Kernel\ValueObjects\Key\ClientId;
use PlugHacker\PlugCore\Kernel\ValueObjects\Key\TestClientId;
use ReflectionClass;

class HubCommandFactory
{
    /**
     *
     * @param  $object
     * @return AbstractCommand
     * @throws \ReflectionException
     */
    public function createFromStdClass($object)
    {
        $commandClass = (new ReflectionClass(AbstractCommand::class))->getNamespaceName();
        $commandClass .= "\\" . $object->command . "Command";

        if (!class_exists($commandClass)) {
            throw new \Exception("Invalid Command class! $commandClass");
        }

        /**
         *
 * @var AbstractCommand $command
*/
        $command = new $commandClass();

        $command->setAccessToken(
            new HubAccessTokenKey($object->access_token)
        );
        $command->setAccountId(
            new AccountId($object->account_id)
        );

        $type = $object->type;
        $command->setType(
            CommandType::$type()
        );

        $clientIdClass = ClientId::class;
        if ($command->getType()->equals(CommandType::Sandbox())) {
            $clientIdClass = TestClientId::class;
        }

        $command->setAccountClientId(
            new $clientIdClass($object->account_client_id)
        );

        $command->setInstallId(
            new GUID($object->install_id)
        );

        $command->setMerchantId(
            new MerchantId($object->merchant_id)
        );

        return $command;
    }
}
