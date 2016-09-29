<?php
namespace Sync\JiraApi;

use \chobie\Jira\Api\Authentication\Basic;

class HtAccessCookieAuth extends Basic
{
    private $userName;
    private $userPass;

    private $cookie;

    /**
     * @param string $userName
     * @param string $userPass
     * @param $htaccess_name
     * @param $htaccess_pass
     */
    public function __construct($userName, $userPass, $htaccess_name, $htaccess_pass)
    {
        parent::__construct($htaccess_name, $htaccess_pass);
        $this->userName = $userName;
        $this->userPass = $userPass;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @return string
     */
    public function getUserPass()
    {
        return $this->userPass;
    }

    /**
     * @return string
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * @return bool
     */
    public function isAuth()
    {
        return $this->cookie !== null;
    }

    /**
     * @param mixed $cookie
     */
    public function setCookie($cookie)
    {
        $this->cookie = $cookie;
    }
}