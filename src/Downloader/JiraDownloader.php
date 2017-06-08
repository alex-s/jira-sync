<?php
namespace Sync\Downloader;


use chobie\Jira\Issues\Walker;
use Sync\JiraApi\AdvancedIssue;

class JiraDownloader extends Downloader
{
    private $projectKey;

    public function __construct($db, $api, $projectKey)
    {
        $this->projectKey = $projectKey;
        parent::__construct($db, $api);
    }

    public function download($isKanban = false)
    {
        $i = 0;
        $sprints = [];
        $issues = [];

        $walker = new Walker($this->api);
        $walker->push(
            "project = {$this->projectKey}"
        );

        foreach ($walker as $issue) {
            $i++;
            if($i%100 == 0) {
                print($i . ' Issues was exported from Jira...'.PHP_EOL);
            }

            /** @var AdvancedIssue $issue */
            $sprints[$issue->getSprintId()] = [
                'jira_id' => $issue->getSprintId(),
                'name' => $issue->getSprintName(),
                'status' => $issue->getSprintStatus()
            ];

            $issues[$issue->getId()] = [
                'jira_id' => $issue->getId(),
                'name' => $issue->getKey() . ' ' . $issue->getSummary(),
                'sprint_jira_id' => !$isKanban ? $issue->getSprintId() : AdvancedIssue::DEFAULT_SPRINT_ID,
                'is_closed' => (int)($issue->getStatus()["name"] == 'Resolved' || $issue->getStatus()["name"] == 'Closed'),
                'estimation' => $issue->getStoryPoints()
            ];
        }

        print($i . ' Issues was exported from Jira.'.PHP_EOL);
        print('Import finish'.PHP_EOL);

        if (!$isKanban) {
            $this->db->insertArray('sprint', $sprints);
        }

        $this->db->insertArray('issue', $issues);
        $this->db->rebuildSprintOrder();
    }
}