<?php

namespace PlugHacker\PlugCore\Kernel\ValueObjects\Key;

use PlugHacker\PlugCore\Kernel\Interfaces\SensibleDataInterface;

final class ClientId extends AbstractClientId implements SensibleDataInterface
{
    protected function validateValue($value)
    {
        return true;
    }

    /**
     *
     * @param string
     * @return string
     */
    public function hideSensibleData($string)
    {
        // TODO: Implement hideSensibleData() method.
    }
}
