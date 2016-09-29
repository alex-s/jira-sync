<?php
namespace Sync\JiraApi;

use chobie\Jira\Api\Exception;
use chobie\Jira\Issue;

class AdvancedIssue extends Issue
{
    const DEFAULT_SPRINT_NAME = 'Unassigned';
    const DEFAULT_SPRINT_ID = 1;
    const DEFAULT_SPRINT_STATUS = 'BACKLOG';


    public function getStatusId($status)
    {
        switch($status) {
            case 'ACTIVE' : return 1;
            case 'FUTURE' : return 2;
            case 'BACKLOG' : return 3;
            case 'CLOSED' : return 4;
            default: throw new Exception('Unsupported sprint status ' . $status);
        }
    }

    public function getSprintName()
    {
        $sprint = $this->get('Sprint');
        $in = $sprint ? $sprint[0] : null;
        preg_match_all("/name=([^,]*),/", $in, $match);

        return isset($match[1]) && isset($match[1][0]) ? $match[1][0] : self::DEFAULT_SPRINT_NAME;
    }

    public function getSprintId()
    {
        $sprint = $this->get('Sprint');
        $in = $sprint ? $sprint[0] : null;
        preg_match_all("/id=([^,]*),/", $in, $match);

        return isset($match[1]) && isset($match[1][0]) ? $match[1][0] : self::DEFAULT_SPRINT_ID;
    }

    public function getSprintStatus()
    {
        $sprint = $this->get('Sprint');
        $in = $sprint ? $sprint[0] : null;
        preg_match_all("/state=([^,]*),/", $in, $match);

        $status = isset($match[1]) && isset($match[1][0]) ? $match[1][0] : self::DEFAULT_SPRINT_STATUS;

        return $this->getStatusId($status);
    }
}