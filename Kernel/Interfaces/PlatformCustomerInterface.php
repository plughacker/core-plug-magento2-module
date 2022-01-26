<?php

namespace PlugHacker\PlugCore\Kernel\Interfaces;

interface PlatformCustomerInterface
{
    public function getCode();
    public function getPlugId();
    public function getName();
    public function getEmail();
    public function getDocument();
    public function getType();
    public function getAddress();
    public function getPhones();
}
