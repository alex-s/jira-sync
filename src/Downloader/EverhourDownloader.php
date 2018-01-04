<?php
namespace Sync\Downloader;

use Monolog\Logger;
use Sync\EverhourApi\Api;
use Sync\Result\EverhourDownloadResult;

class EverhourDownloader extends Downloader
{
    public function download(Logger $logger)
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
                'status' => (int) ($section['status'] === 'open'),
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
                'status' => (int) ($task['status'] === 'open'),
            ];
        }

        return new EverhourDownloadResult($sectionBuffer, $issueBuffer, $users);
    }

    public function uploadSections(Logger $logger, $newSections, $updatedSections)
    {
        /** @var Api $api */
        $api = $this->api;

        foreach ($newSections as $section) {
            $logger->info(sprintf(' - create section %s', $section['name']));
            $api->createSection(['name' => $section['name']]);
            sleep(0.1);
        }

        foreach ($updatedSections as $section) {
            $logger->info(sprintf(' - update section %s', $section['name']));
            $data = ['name' => $section['name'], 'position' => $section['position'], 'status' => $section['status'] ? 'open' : 'archived'];
            $api->updateSection($section['everhour_id'], $data);
            sleep(0.1);
        }
    }

    public function uploadIssues(Logger $logger, $newIssues, $updatedIssues)
    {
        /** @var Api $api */
        $api = $this->api;

        foreach ($newIssues as $issue) {
            $logger->info(sprintf(' - create issue %s', $issue['name']));
            $api->newIssue(['name' => $issue['name'], 'section' => $issue['sprint_everhour_id']]);
            sleep(0.1);
        }

        foreach ($updatedIssues as $issue) {
            $logger->info(sprintf(' - update issue %s', $issue['name']));
            $data = ['name' => $issue['name'], 'status' => $issue['status'] ? 'open' : 'closed', 'section' => $issue['sprint_everhour_id']];
            $api->updateIssue($issue['issue_everhour_id'], $data);
            sleep(0.1);
        }
    }
}