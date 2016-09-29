<?php
namespace Sync\JiraApi;

use chobie\Jira\Api\Result;

class AdvancedResult extends Result
{
    public function getIssues()
    {
        $result = [];

        if (isset($this->result['issues'])) {
            foreach ($this->result['issues'] as $issue) {
                $result[] = new AdvancedIssue($issue);
            }
        }

        return $result;
    }
}