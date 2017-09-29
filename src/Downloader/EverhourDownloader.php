<?php
namespace Sync\Downloader;

use Sync\EverhourApi\Api;

class EverhourDownloader extends Downloader
{
    public function download($isKanban = false)
    {
        /** @var Api $api */
        $api = $this->api;
        $sections = $api->getSections();
        $everhourUsers = $api->getUsers();
        $everhourTasks = $api->getTasks();
        $sectionBuffer = [];
        $issueBuffer = [];
        $users = [];

        foreach ($everhourUsers as $user) {
            $users[] = [
                'id' => $user['id'],
                'name' => $user['name'],
            ];
        }

        foreach ($sections as $section) {
            $sectionBuffer[] = [
                'everhour_id' => $section['id'],
                'name' => $section['name'],
            ];
        }

        foreach($everhourTasks as $task) {
            $timeSpent = isset($task['time']['total']) ? $task['time']['total'] : 0;
            $mostTrackedTime = 0;
            $mostTrackedUserId = 0;

            if (isset($task['time']['users'])) {
                foreach ($task['time']['users'] as $userId => $time) {
                    if ($time > $mostTrackedTime) {
                        $mostTrackedTime = $time;
                        $mostTrackedUserId = $userId;
                    }
                }
            }

            $issueBuffer[] = [
                'everhour_id' => $task['id'],
                'name' => $task['name'],
                'time_spent' => $timeSpent,
                'user_id' => $mostTrackedUserId,
            ];
        }

        $this->db->clearTable('sprint_buffer');
        $this->db->clearTable('issue_buffer');

        $this->db->insertArray('sprint_buffer', $sectionBuffer);
        $this->db->insertArray('issue_buffer', $issueBuffer);
        $this->db->insertArray('user', $users);

        $this->db->mergeSprintBuffer();
        $this->db->mergeIssueBuffer();
    }

    public function upload($isKanban)
    {
        /** @var Api $api */
        $api = $this->api;
        $newSections = $this->db->getSprintData(false);
        $requests = 0;

        $count = count($newSections);
        $i = 1;
        foreach ($newSections as $section) {
            $requests++;
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
            $requests++;
            print(sprintf('update %d section from %d' . PHP_EOL, $i, $count));
            $data = ['name' => $section['name'], 'position' => $section['position'], 'status' => !$isKanban ? ($section['status'] > 3 ? 'archived' : 'open') : 'open'];
            $api->updateSection($section['everhour_id'], $data);
            sleep(0.1);
            $i++;
        }

        $issues = $this->db->getIssueData();
        $count = count($issues);
        $i = 1;
        foreach ($issues as $issue) {
            print(sprintf('%s %d issue from %d' . PHP_EOL, empty($issue['everhour_id']) ? 'create' : 'update', $i, $count));

            $data = ['name' => $issue['name'], 'status' => $issue['is_closed'] ? 'closed' : 'open', 'section' => $issue['sprint_everhour_id']];

            if (empty($issue['everhour_id']) && !$issue['is_closed']) {
                $requests++;
                $api->createIssue($data);
            }

            if (!empty($issue['everhour_id']) && ($issue['is_closed'] || $issue['is_changed'])) {
                $api->updateIssue($issue['everhour_id'], $data);
            }

            sleep(0.1);
            $i++;
        }

        print("everhour sync 2".  PHP_EOL);
        $this->download();

        return $requests;
    }
}