<?php
namespace Sync\EverhourApi;

use chobie\Jira\Api\Authentication\Basic;
use chobie\Jira\Api\Exception;

class ApiKeyAuth extends Basic
{
    private $apiKey;

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

    /**
     * @param mixed $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function parseResponse($response)
    {
        preg_match_all('/everhour_api_key=([^,]*); path/', $response, $key);

        if (!isset($key[1]) || !isset($key[1][0])) {
            throw new Exception("Can't parse auth response: '{$response}'");
        }

        return '{"key":"' . $key[1][0]. '"}';
    }


}