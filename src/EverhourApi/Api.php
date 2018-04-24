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

    public function getSections()
    {
        $result = $this->api(self::REQUEST_GET, "/projects/{$this->projectKey}/sections");
        return $this->checkResult($result->getResult());
    }

    public function createSection($data)
    {
        $result = $this->api(self::REQUEST_POST, "/projects/{$this->projectKey}/sections", $data);
        $this->checkResult($result->getResult());

        return $result->getResult()->id;
    }

    public function updateSection($id, $data)
    {
        $result = $this->api(self::REQUEST_PUT, "/sections/{$id}", $data);
        $this->checkResult($result->getResult());
    }

    public function newIssue($data)
    {
        $result = $this->api(self::REQUEST_POST, "/projects/{$this->projectKey}/tasks", $data);
        $this->checkResult($result->getResult());
    }

    public function updateIssue($id, $data)
    {
        $result = $this->api(self::REQUEST_PUT, "/tasks/{$id}", $data);
        $this->checkResult($result->getResult());
    }

    public function getUsers()
    {
        $result =  $this->api(self::REQUEST_GET, "/team/users");
        return $this->checkResult($result->getResult());
    }

    public function getTasks()
    {
        $result = $this->api(self::REQUEST_GET, "/projects/{$this->projectKey}/tasks");
        return $this->checkResult($result->getResult());
    }

    private function checkResult($result)
    {
        if (isset($result['errors'])) {
            throw new \Exception(json_encode($result, JSON_PRETTY_PRINT));
        }

        if (isset($result['code'])) {
            throw new \Exception($result['message']);
        }

        return $result;
    }
}