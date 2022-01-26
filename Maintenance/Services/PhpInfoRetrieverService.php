<?php

namespace PlugHacker\PlugCore\Maintenance\Services;

use PlugHacker\PlugCore\Maintenance\Interfaces\InfoRetrieverServiceInterface;

class PhpInfoRetrieverService implements InfoRetrieverServiceInterface
{
    public function retrieveInfo($value)
    {
        ob_start();
        phpinfo();
        $phpinfoAsString = ob_get_contents();
        ob_get_clean();

        return $phpinfoAsString;
    }
}
