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
        return $this->api(self::REQUEST_GET, "/internal-projects/{$this->projectKey}/sections")->getResult();
    }

    public function createSection($data)
    {
        $this->api(self::REQUEST_POST, "/internal-projects/{$this->projectKey}/sections", $data);
    }

    public function updateSection($id, $data)
    {
        $this->api(self::REQUEST_PUT, "/sections/{$id}", $data);
    }

    public function createIssue($data)
    {
        $this->api(self::REQUEST_POST, "/internal-projects/{$this->projectKey}/tasks", $data);
    }

    public function updateIssue($id, $data)
    {
        $this->api(self::REQUEST_PUT, "/tasks/{$id}", $data);
    }
}