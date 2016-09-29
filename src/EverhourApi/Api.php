<?php
namespace Sync\EverhourApi;

use chobie\Jira\Api as JiraApi;

class Api extends JiraApi
{
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

}