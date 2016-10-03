<?php
namespace Sync\Downloader;

use Sync\EverhourApi\Api;

class EverhourDownloader extends Downloader
{
    public function download()
    {
        /** @var Api $api */
        $api = $this->api;
        $sections = $api->getSections();

        $sectionBuffer = [];
        $issueBuffer = [];

        foreach ($sections as $section) {
            $sectionBuffer[] = [
                'everhour_id' => $section['id'],
                'name' => $section['name'],
            ];

            if (isset($section['tasks'])) {
                foreach ($section['tasks'] as $issue) {
                    $issueBuffer[] = [
                        'everhour_id' => $issue['id'],
                        'name' => $issue['name'],
                    ];
                }
            }

        }
        $this->db->clearTable('sprint_buffer');
        $this->db->clearTable('issue_buffer');

        $this->db->insertArray('sprint_buffer', $sectionBuffer);
        $this->db->insertArray('issue_buffer', $issueBuffer);

        $this->db->mergeBuffer('sprint');
        $this->db->mergeBuffer('issue');
    }

    public function upload()
    {
        /** @var Api $api */
        $api = $this->api;
        $newSections = $this->db->getSprintData(false);


        $count = count($newSections);
        $i = 1;
        foreach ($newSections as $section) {
            print(sprintf('create %d section from %d' . PHP_EOL, $i, $count));
            $data = ['name' => $section['name']];
            $api->createSection($data);
            sleep(0.1);
            $i++;
        }

        print("everhour sync 1" . PHP_EOL);
        $this->download();

        $allSections = $this->db->getSprintData();
        $count = count($allSections);
        $i = 1;
        foreach ($allSections as $section) {
            print(sprintf('update %d section from %d' . PHP_EOL, $i, $count));
            $data = ['position' => $section['position'], 'status' => $section['status'] > 3 ? 'archived' : 'open'];
            $api->updateSection($section['everhour_id'], $data);
            sleep(0.1);
            $i++;
        }

        $issues = $this->db->getIssueData();
        $count = count($issues);
        $i = 1;
        foreach ($issues as $issue) {
            print(sprintf('%s %d issue from %d' . PHP_EOL, empty($issue['everhour_id']) ? 'create' : 'update', $i, $count));

            $data = ['name' => $issue['name'], 'status' => $issue['is_closed'] ? 'closed' : 'open', 'section' => ['id' => $issue['sprint_everhour_id']]];

            if (empty($issue['everhour_id'])) {
                $api->createIssue($data);
            } else {
                $api->updateIssue($issue['issue_everhour_id'], $data);
            }
            sleep(0.1);
            $i++;
        }

        print("everhour sync 2".  PHP_EOL);
        $this->download();
    }
}