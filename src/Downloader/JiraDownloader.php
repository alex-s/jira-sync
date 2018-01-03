<?php
namespace Sync\Downloader;


use chobie\Jira\Issues\Walker;
use Monolog\Logger;
use Sync\JiraApi\AdvancedIssue;
use Sync\Result\JiraDownloadResult;

class JiraDownloader extends Downloader
{
    private $projectKey;
    private $offset;

    public function __construct($api, $projectKey, $offset)
    {
        $this->projectKey = $projectKey;
        $this->offset = $offset;
        parent::__construct($api);
    }

    public function download(Logger $logger = null)
    {
        $sprints = [];
        $issues = [];

        $walker = new Walker($this->api);
        $walker->push($this->getFilter());
        $i = 0;
        foreach ($walker as $issue) {
            $i++;

            if ($i % 100 == 0) {
                $logger->info(sprintf(' - download %d issues', $i));
            }

            $sprint = $issue->getSprintId();
            /** @var AdvancedIssue $issue */
            $sprints[$sprint] = [
                'jira_id' => $sprint,
                'name' => $issue->getSprintName(),
                'status' => $issue->getSprintStatus()
            ];
            $issueId = $issue->getId();
            $status = $issue->getStatus()['name'];

            $issues[$issueId] = [
                'jira_id' => $issueId,
                'name' => $issue->getKey() . ' ' . $issue->getSummary(),
                'sprint_jira_id' => $issue->getSprintId(),
                'status' => (int) !($status === 'Resolved' || $status === 'Closed'),
                'estimation' => $issue->getStoryPoints()
            ];
        }

        return new JiraDownloadResult($sprints, $issues);
    }

    public function getFilter()
    {
        return "project = {$this->projectKey} AND id >= {$this->projectKey}-{$this->offset}";
    }
}