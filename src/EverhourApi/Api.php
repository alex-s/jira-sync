<?php
namespace Sync\EverhourApi;

use chobie\Jira\Api as JiraApi;
use chobie\Jira\Api\Authentication\AuthenticationInterface;
use chobie\Jira\Api\Client\ClientInterface;

class Api extends JiraApi
{
    private $projectKey;

    public function __construct($endpoint, $projectKey, AuthenticationInterface $authentication, ClientInterface $client = null)
    {
        $this->projectKey = $projectKey;
        parent::__construct($endpoint, $authentication, $client);
    }


    public function api($method = self::REQUEST_GET, $url, $data = array(), $return_as_array = false, $is_file = false, $debug = false)
    {
        /** @var ApiKeyAuth $auth */
        $auth = $this->authentication;

        if (!$auth->isAuth()) {
            $authData =  parent::api(self::REQUEST_POST, '/auth/password/authorize', ["email" => $auth->getId(), "password" => $auth->getPassword()], true);

            if ($authData['key']) {
                $auth->setApiKey($authData['key']);
            } else {
                throw new JiraApi\UnauthorizedException('Can\'t get API KEY value');
            }
        }

        return parent::api($method,$url,$data,$return_as_array,$is_file,$debug);
    }


    public function getSections()
    {
        return $this->api(self::REQUEST_GET, "/internal-projects/{$this->projectKey}/sections")->getResult();
    }
}