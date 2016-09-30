<?php
namespace Sync\EverhourApi;

use chobie\Jira\Api\Authentication\Basic;

class ApiKeyAuth extends Basic
{
    private $apiKey;

    public function __construct($apiKey)
    {
       $this->apiKey = $apiKey;
    }

    /**
     * @return mixed
     */
    public function isAuth()
    {
        return $this->apiKey !==null;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }
}