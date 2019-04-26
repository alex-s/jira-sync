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
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Formatter\LineFormatter;

$logger = new Logger('default');
$handler = new StreamHandler('php://stdout');
$output = "[%datetime%] %level_name%: %message%\n";
$formatter = new LineFormatter($output);
$handler->setFormatter($formatter);
$logger->pushHandler($handler);

$startTime = time();

$params = parse_ini_file('params.ini');
//@TODO validate params

$db = new DB($params['db_host'], $params['db_user'], $params['db_pass'], $params['db_database']);
$db->execFile(file_get_contents('db.sql'));

$jiraApi = new AdvancedApi($params['jira_url'],
    new \chobie\Jira\Api\Authentication\Basic($params['jira_login'], $params['jira_password']),
//    new HtAccessCookieAuth($params['jira_login'], $params['jira_password'], $params['htaccess_user'], $params['htaccess_pass']),
    new AdvancedCurlClient()
);

$everhourApi = new Api($params['eh_url'], $params['eh_project_key'],
    new ApiKeyAuth($params['eh_api_key']),
    new AdvancedCurlClient()
);

$jiraSync = new JiraDownloader($jiraApi, $params['jira_project_key'], 3500);
$logger->info('Download issues from Jira');
$logger->info(sprintf(' - with filter "%s"', $jiraSync->getFilter()));
$jiraIssues = $jiraSync->download($logger);
$logger->info($jiraIssues);
$db->createJiraEntries($jiraIssues);


$everhourSync = new EverhourDownloader($everhourApi);
$logger->info('Download issues from Everhour');
$everhourIssues = $everhourSync->download($logger);
$logger->info($everhourIssues);
$db->createBufferEntries($everhourIssues);


$db->mergeSprintBuffer();
$logger->info('Upload Sections');

$newSection = $db->getNewSections();
$updatedSection = $db->getUpdatedSections();
$updates = $everhourSync->uploadSections($logger, $newSection, $updatedSection);
$db->applySectionsUpdates($updates);
$logger->info(sprintf(' - created %d sections, updated %d sections', count($newSection), count($updatedSection)));


$db->mergeIssueBuffer();
$logger->info('Upload Issues');
$newIssues = $db->getNewIssues();
$updatedIssues = $db->getUpdatedIssues();
$everhourSync->uploadIssues($logger, $newIssues, $updatedIssues);
$logger->info(sprintf(' - created %d issues, updated %d issues', count($newIssues), count($updatedIssues)));


$logger->info('Done');
$logger->info(sprintf('memory usage: %s kb, time: %f min' . PHP_EOL, memory_get_usage() /100, (time()-$startTime)/60));