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
        return $this->api(self::REQUEST_GET, "/projects/{$this->projectKey}/sections")->getResult();
    }

    public function createSection($data)
    {
        $result = $this->api(self::REQUEST_POST, "/projects/{$this->projectKey}/sections", $data);
        $this->checkResult($result->getResult());
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
        $this->api(self::REQUEST_PUT, "/tasks/{$id}", $data);
    }

    public function getUsers()
    {
        return $this->api(self::REQUEST_GET, "/team/users")->getResult();
    }

    public function getTasks()
    {
        return $this->api(self::REQUEST_GET, "/projects/{$this->projectKey}/tasks")->getResult();
    }

    private function checkResult($result)
    {
        if (isset($result['errors'])) {
            throw new \Exception(json_encode($result, JSON_PRETTY_PRINT));
        }
    }
}