<?php
namespace Sync\EverhourApi;

class Client
{
    //https://api.everhour.com/internal-projects/ev:167438714416270/sections
    //https://api.everhour.com/auth/password/authorize
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