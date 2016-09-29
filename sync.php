<?php

require_once 'load.php';

use chobie\Jira\Api;
use chobie\Jira\Issues\Walker;
use \Sync\JiraApi\AdvancedCurlClient;
use \Sync\JiraApi\AdvancedApi;
use \Sync\JiraApi\AdvancedIssue;
use \Sync\Database\DB;
use \Sync\JiraApi\HtAccessCookieAuth;

$params = parse_ini_file('params.ini');
//@TODO validate params

$sprints = [];
$issues = [];
$db = new DB($params['db_host'], $params['db_user'], $params['db_pass'], $params['db_database']);
$db->execFile(file_get_contents('db.sql'));

$api = new AdvancedApi($params['jira_url'],
    new HtAccessCookieAuth($params['jira_login'], $params['jira_password'], $params['htaccess_user'], $params['htaccess_pass']),
    new AdvancedCurlClient()
);

//$api = new \Sync\EverhourApi\Api($params['eh_url'],
//    new \Sync\EverhourApi\ApiKeyAuth($params['eh_login'], $params['eh_password']),
//    new AdvancedCurlClient()
//);

//$result = $api->api('GET', '/internal-projects/ev:167438714416270/sections');
//var_dump($result->getResult());




//var_dump($db->fetch("SELECT 1"));
//die;

$walker = new Walker($api);
$walker->push(
    "project = {$params['jira_project_key']}"
);
$i = 0;

//foreach ( $walker as $issue ) {
//    $i++;
//
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
//        'sprint_id' => $issue->getSprintId(),
//        'is_open' => !($issue->getStatus()["name"] == 'Resolved' || $issue->getStatus()["name"] == 'Closed')
//    ];
//}

print($i . ' Issues was exported from Jira'.PHP_EOL);

$sprints[1] = [
    'jira_id' => 1,
    'name' => 2,
    'status' => 3
];

$sprints[2] = [
    'jira_id' => 2,
    'name' => 3,
    'status' => 4
];

$sprints[3] = [
    'jira_id' => 3,
    'name' => 4,
    'status' => 5
];

$sprints[4] = [
    'jira_id' => 4,
    'name' => 5,
    'status' => 6
];

$sprints[5] = [
    'jira_id' => 5,
    'name' => 6,
    'status' => 9
];

$db->insertArray('sprint', $sprints);
$db->insertArray('sprint', $issues);
//$count = $db->insertIssues($sprints);

//var_dump("Empty Result");
//var_dump($issues);
//var_dump($milestones);
