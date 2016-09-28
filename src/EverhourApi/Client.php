<?php
namespace Sync\EverhourApi;

class Client
{
    private $login;
    private $password;

    private $apiKey;

    public function __construct($url, $login, $password)
    {
        $this->url = $url;
        $this->login = $login;
        $this->password = $password;

        $this->init();
    }

    private function init()
    {

    }
}