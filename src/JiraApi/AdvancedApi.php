<?php
namespace Sync\JiraApi;

use chobie\Jira\Api;

class AdvancedApi extends Api
{
    public function api($method = self::REQUEST_GET, $url, $data = array(), $return_as_array = false, $is_file = false, $debug = false)
    {
        /** @var HtAccessCookieAuth $auth */
        $auth = $this->authentication;

//        if (!$auth->isAuth()) {
//            $authData =  parent::api(self::REQUEST_POST, '/rest/auth/1/session', ["username" => $auth->getUserName(), "password" => $auth->getUserPass()], true);
//
//            if ($authData['session'] && $authData['session']['value']) {
//                $auth->setCookie($authData['session']['value']);
//            } else {
//                throw new Api\UnauthorizedException('Can\'t get cookie value');
//            }
//        }

        $result = parent::api($method,$url,$data,$return_as_array,$is_file,$debug);

        if ($return_as_array) {
            return $result;
        }

        return new AdvancedResult($result->getResult());
    }
}