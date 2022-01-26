<?php

namespace PlugHacker\PlugCore\Hub\Commands;


use PlugHacker\PlugCore\Kernel\Interfaces\CommandInterface;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\AccountId;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\MerchantId;
use PlugHacker\PlugCore\Kernel\ValueObjects\Id\GUID;
use PlugHacker\PlugCore\Kernel\ValueObjects\Key\HubAccessTokenKey;
use PlugHacker\PlugCore\Kernel\ValueObjects\Key\ClientId;
use PlugHacker\PlugCore\Kernel\ValueObjects\Key\TestClientId;

abstract class AbstractCommand implements CommandInterface
{
    /**
     *
     * @var HubAccessTokenKey
     */
    protected $accessToken;
    /**
     *
     * @var AccountId
     */
    protected $accountId;
    /**
     *
     * @var ClientId|TestClientId
     */
    protected $accountClientId;
    /**
     *
     * @var GUID
     */
    protected $installId;
    /**
     *
     * @var MerchantId
     */
    protected $merchantId;
    /**
     *
     * @var CommandType
     */
    protected $type;

    /**
     *
     * @return HubAccessTokenKey
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     *
     * @param  HubAccessTokenKey $accessToken
     * @return AbstractCommand
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     *
     * @return AccountId
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     *
     * @param  AccountId $accountId
     * @return AbstractCommand
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
        return $this;
    }

    /**
     *
     * @return ClientId|TestClientId
     */
    public function getAccountClientId()
    {
        return $this->accountClientId;
    }

    /**
     *
     * @param  ClientId|TestClientId $accountClientId
     * @return AbstractCommand
     */
    public function setAccountClientId($accountClientId)
    {
        $this->accountClientId = $accountClientId;
        return $this;
    }

    /**
     *
     * @return GUID
     */
    public function getInstallId()
    {
        return $this->installId;
    }

    /**
     *
     * @param  GUID $installId
     * @return AbstractCommand
     */
    public function setInstallId($installId)
    {
        $this->installId = $installId;
        return $this;
    }

    /**
     *
     * @return MerchantId
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     *
     * @param  MerchantId $merchantId
     * @return AbstractCommand
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
        return $this;
    }

    /**
     *
     * @return CommandType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @param  CommandType $type
     * @return AbstractCommand
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
