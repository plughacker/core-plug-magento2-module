<?php

namespace PlugHacker\PlugCore\Kernel\Factories\Configurations;

use PlugHacker\PlugCore\Kernel\Interfaces\FactoryCreateFromDbDataInterface;
use PlugHacker\PlugCore\Kernel\ValueObjects\Configuration\PixConfig;

class PixConfigFactory implements FactoryCreateFromDbDataInterface
{
    /**
     * @param object $data
     * @return PixConfig
     */
    public function createFromDbData($data)
    {
        $pixConfig = new PixConfig();

        if (isset($data->enabled)) {
            $pixConfig->setEnabled((bool) $data->enabled);
        }

        if (!empty($data->title)) {
            $pixConfig->setTitle($data->title);
        }

        if (!empty($data->expirationQrCode)) {
            $pixConfig->setExpirationQrCode($data->expirationQrCode);
        }

        return $pixConfig;
    }
}
