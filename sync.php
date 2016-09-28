<?php

require_once 'load.php';

use chobie\Jira\Api;
use chobie\Jira\Issues\Walker;
use chobie\Jira\Issue;
use \Sync\JiraApi\CookieAuth;
use \Sync\JiraApi\AdvancedCurlClient;
use \Sync\JiraApi\AdvancedApi;

$milestones = [];
$issues = [];

const DEFAULT_MILESTONE_NAME = 'Empty Milestone';
const DEFAULT_MILESTONE_ID = 1;

$params = parse_ini_file('params.ini');
//@TODO validate params

//var_dump($params);

$api = new AdvancedApi($params['jira_url'],
    new CookieAuth($params['jira_login'], $params['jira_password'], $params['htaccess_user'], $params['htaccess_pass']),
    new AdvancedCurlClient()
);

$walker = new Walker($api);
$walker->push(
    "project = {$params['jira_project_key']}"
);
$i = 0;
foreach ( $walker as $issue ) {
    $i++;
    var_dump($issue);
    /** @var Issue $issue */
    $sprint = $issue->get('Sprint');

    $in = $sprint ? $sprint[0] : null;
    preg_match_all("/name=([^,]*),/", $in, $name);
    $name = isset($name[1]) && isset($name[1][0]) ? $name[1][0] : DEFAULT_MILESTONE_NAME;

    preg_match_all("/id=([^,]*),/", $in, $id);
    $id = isset($id[1]) && isset($id[1][0]) ? $id[1][0] : DEFAULT_MILESTONE_ID;

    $milestones[$id] = ['id' => $id, 'name' => $name];

    $isOpen = !($issue->getStatus()["name"] == 'Resolved' || $issue->getStatus()["name"] == 'Closed');

    $issues[$issue->getId()] = [
        'id' => $issue->getId(),
        'name' => $issue->getKey() . ' ' . $issue->getSummary(),
        'status' => $isOpen,
        'milestone_id' => $id
    ];
    print($i.PHP_EOL);
    die;
//    die;
    // Send custom notification here.
}

var_dump("Empty Result");
//var_dump($issues);
//var_dump($milestones);
