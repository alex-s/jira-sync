<?php

require_once 'load.php';

use \Sync\JiraApi\AdvancedCurlClient;
use \Sync\JiraApi\AdvancedApi;
use \Sync\Database\DB;
use \Sync\JiraApi\HtAccessCookieAuth;
use \Sync\EverhourApi\Api;
use \Sync\EverhourApi\ApiKeyAuth;
use \Sync\Downloader\JiraDownloader;
use \Sync\Downloader\EverhourDownloader;

$startTime = time();

$params = parse_ini_file('params.ini');
//@TODO validate params

$db = new DB($params['db_host'], $params['db_user'], $params['db_pass'], $params['db_database']);
$db->execFile(file_get_contents('db.sql'));

$jiraApi = new AdvancedApi($params['jira_url'],
    new HtAccessCookieAuth($params['jira_login'], $params['jira_password'], $params['htaccess_user'], $params['htaccess_pass']),
    new AdvancedCurlClient()
);
$isKanban = isset($argv[1]) && $argv[1] == 'kanban';

print('Run in ' . ($isKanban ? 'KANBAN':'AGILE') . ' mode' . PHP_EOL);

$everhourApi = new Api($params['eh_url'], $params['eh_project_key'],
    new ApiKeyAuth($params['eh_api_key']),
    new AdvancedCurlClient()
);

$jiraSync = new JiraDownloader($db, $jiraApi, $params['jira_project_key']);
$everhourSync = new EverhourDownloader($db, $everhourApi);

print('Dowload issues from Jira'.PHP_EOL);
$jiraSync->download($isKanban);

print('Sync issues with everhour'.PHP_EOL);
$everhourSync->download();

print('Upload issues to everhour'.PHP_EOL);
$requests = $everhourSync->upload($isKanban);

print("Done".PHP_EOL);
print("Requests made: {$requests}".PHP_EOL);
print(sprintf("memory: %s kb, time: %f min" . PHP_EOL, memory_get_usage() /100, (time()-$startTime)/60));