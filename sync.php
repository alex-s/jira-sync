<?php

require_once 'load.php';

use chobie\Jira\Issues\Walker;
use \Sync\JiraApi\AdvancedCurlClient;
use \Sync\JiraApi\AdvancedApi;
use \Sync\Database\DB;
use \Sync\JiraApi\HtAccessCookieAuth;
use \Sync\EverhourApi\Api;
use \Sync\EverhourApi\ApiKeyAuth;

$params = parse_ini_file('params.ini');
//@TODO validate params

$sprints = [];
$issues = [];
$db = new DB($params['db_host'], $params['db_user'], $params['db_pass'], $params['db_database']);
$db->execFile(file_get_contents('db.sql'));

$jiraApi = new AdvancedApi($params['jira_url'],
    new HtAccessCookieAuth($params['jira_login'], $params['jira_password'], $params['htaccess_user'], $params['htaccess_pass']),
    new AdvancedCurlClient()
);

$everhourApi = new Api($params['eh_url'], $params['eh_project_key'],
    new ApiKeyAuth($params['eh_api_key']),
    new AdvancedCurlClient()
);

$walker = new Walker($jiraApi);
$walker->push(
    "project = {$params['jira_project_key']}"
);
$i = 0;

//foreach ( $walker as $issue ) {
//    $i++;
//    if($i%100 == 0) {
//        print($i . ' Issues was exported from Jira...'.PHP_EOL);
//    }
//    /** @var AdvancedIssue $issue */
//    $sprints[$issue->getSprintId()] = [
//        'jira_id' => $issue->getSprintId(),
//        'name' => $issue->getSprintName(),
//        'status' => $issue->getSprintStatus()
//    ];
//
//    $issues[$issue->getId()] = [
//        'jira_id' => $issue->getId(),
//        'name' => $issue->getKey() . ' ' . $issue->getSummary(),
//        'sprint_jira_id' => $issue->getSprintId(),
//        'is_closed' => $issue->getStatus()["name"] == 'Resolved' || $issue->getStatus()["name"] == 'Closed'
//    ];
//}

print($i . ' Issues was exported from Jira.'.PHP_EOL);
print('Import finish'.PHP_EOL);

//$db->insertArray('sprint', $sprints);
//$db->insertArray('issue', $issues);
//$db->rebuildSprintOrder();

$sections = $everhourApi->getSections();

$sectionBuffer = [];
$issueBuffer = [];
foreach ($sections as $section) {
    $sectionBuffer[] = [
        'everhour_id' => $section['id'],
        'name' => $section['name'],
    ];

    foreach ($section['tasks'] as $issue) {
        $issueBuffer[] = [
            'everhour_id' => $issue['id'],
            'name' => $issue['name'],
        ];
    }
}

$db->insertArray('sprint_buffer', $sectionBuffer);
$db->insertArray('issue_buffer', $issueBuffer);

$db->mergeBuffer('sprint');
$db->mergeBuffer('issue');

//$newSections = $db->getNewSprints();
//$everhourApi->newSections($newSections);
//$newissues = $db->getInserts('sprint');

//var_dump("Empty Result");
//var_dump($issues);
//var_dump($milestones);
