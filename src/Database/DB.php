<?php
namespace Sync\Database;


use chobie\Jira\Api\Exception;
use Sync\Result\EverhourDownloadResult;
use Sync\Result\JiraDownloadResult;

class DB
{
    private $connection;

    private $host;
    private $user;
    private $pass;
    private $database;

    public function __construct($host, $user, $pass, $database)
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->database = $database;
    }


    private function getConnection()
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $connection = new \mysqli($this->host, $this->user, $this->pass, $this->database);

        if($connection->connect_errno > 0){
            throw new Exception($connection->connect_error);
        }

        $this->connection = $connection;

        return $this->connection;
    }

    public function fetch($sql)
    {
        return $this->exec($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function exec($sql)
    {
        $result = $this->getConnection()->query($sql);

        if (!$result) {
            throw new Exception($this->getConnection()->error);
        }

        return $result;
    }

    public function execFile($sqls)
    {
        foreach (explode(';', $sqls) as $sql) {
            if (!empty($sql)) {
                $this->exec($sql);
            }
        }
    }

    public function insertArray($table, array $rows)
    {
        if (empty($rows)) {
            return;
        }

        $keys = array_keys($rows[array_keys($rows)[0]]);
        $insertHeaders = implode('`,`', $keys);
        $duplicates = [];

        foreach ($keys as $key) {
            if ($key == 'jira_id') {
                continue;
            }
            $duplicates[] = sprintf("`%s` = VALUES(%s)", $key, $key);
        }

        $duplicates = implode(',', $duplicates);


        $sqlTemplate = <<<SQL
            INSERT INTO {$table} (`{$insertHeaders}`)
            VALUES :values
            ON DUPLICATE KEY UPDATE {$duplicates}

SQL;
        $values = [];
        $i = 0;
        foreach ($rows as $row) {
            $i++;

            $row = array_map([$this->getConnection(), 'escape_string'], $row);
            $values[] = vsprintf("(" . implode(",", array_fill(0, count($keys), "'%s'")) . ")", $row);

            if (($i % 50 == 0) || $i == count($rows)) {
                $sql = str_replace(':values', implode(',', $values), $sqlTemplate);

                $this->exec($sql);
                $values = [];
            }
        }
    }

    public function rebuildSprintOrder()
    {
        $sql = <<<SQL
            UPDATE `sprint`
            LEFT JOIN (
                SELECT jira_id, @a := @a+1 as num FROM `sprint`
                ORDER BY status
            ) positions ON positions.jira_id = sprint.jira_id
            SET position = positions.num
SQL;
        $this->exec('SET @a = 0;');
        $this->exec($sql);
    }

    public function mergeSprintBuffer()
    {
        $sql = <<<SQL
            UPDATE sprint
            JOIN sprint_buffer ON sprint.everhour_id = sprint_buffer.everhour_id
            SET 
                publish_status = IF(sprint.name != sprint_buffer.name OR sprint.status != sprint_buffer.status, 1, 2)
SQL;
        $this->exec($sql);

        $sql = <<<SQL
            UPDATE sprint
            JOIN sprint_buffer ON sprint.name = sprint_buffer.name
            SET 
                publish_status = IF(sprint.status != sprint_buffer.status, 1, 2),
                sprint.everhour_id = sprint_buffer.everhour_id
            WHERE sprint.everhour_id IS NULL
SQL;
        $this->exec($sql);

        $sql = <<<SQL
            DELETE sprint_buffer FROM sprint_buffer
            JOIN sprint ON sprint.everhour_id = sprint_buffer.everhour_id
SQL;
        $this->exec($sql);
    }

    public function mergeIssueBuffer()
    {
        $sql = <<<SQL
            UPDATE issue
            JOIN issue_buffer on issue.everhour_id = issue_buffer.everhour_id
            JOIN sprint ON sprint.jira_id = issue.sprint_jira_id
            SET 
                issue.publish_status = IF(
                    issue.name != issue_buffer.name 
                    OR issue.status != issue_buffer.status 
                    OR issue_buffer.sprint_everhour_id != sprint.everhour_id,
                1, 2),
                issue.time_spent = issue_buffer.time_spent, 
                issue.user_id = issue_buffer.user_id,
                issue.sprint_everhour_id = sprint.everhour_id
            WHERE issue.everhour_id IS NOT NULL AND issue.everhour_id != ''
SQL;
        $this->exec($sql);

        $sql = <<<SQL
            UPDATE issue
            JOIN issue_buffer on issue.name = issue_buffer.name
            JOIN sprint ON sprint.jira_id = issue.sprint_jira_id
            SET 
                issue.publish_status = IF(
                    issue.status != issue_buffer.status OR 
                    issue_buffer.sprint_everhour_id != sprint.everhour_id,
                1, 2),
                issue.everhour_id = issue_buffer.everhour_id, 
                issue.sprint_everhour_id = sprint.everhour_id,
                issue.time_spent = issue_buffer.time_spent, 
                issue.user_id = issue_buffer.user_id
            WHERE issue.everhour_id IS NULL OR issue.everhour_id = ''
SQL;
        $this->exec($sql);
    }

    public function clearTable($table)
    {
        $this->exec("DELETE FROM {$table}");
    }

    public function createJiraEntries(JiraDownloadResult $result)
    {
        $this->insertArray('sprint', $result->getSprints());
        $this->insertArray('issue', $result->getIssues());

        $this->rebuildSprintOrder();
    }

    public function createBufferEntries(EverhourDownloadResult $result)
    {
        $this->clearTable('sprint_buffer');
        $this->clearTable('issue_buffer');

        $this->insertArray('sprint_buffer', $result->getSections());
        $this->insertArray('issue_buffer', $result->getIssues());
        $this->insertArray('user', $result->getUsers());
    }

    public function getNewSections()
    {
        $sql = <<<SQL
            SELECT name FROM sprint
            WHERE publish_status = 0 AND status = 1
SQL;
        return $this->fetch($sql);
    }

    public function getUpdatedSections()
    {
        $sql = <<<SQL
            SELECT everhour_id, name, status, position FROM sprint
            WHERE publish_status = 1
            UNION 
            SELECT everhour_id, name, 0 as status, 999 as position FROM sprint_buffer
            WHERE status = 1
SQL;
        return $this->fetch($sql);
    }

    public function applySectionsUpdates($updates)
    {
        $sql = <<<SQL
            UPDATE sprint SET everhour_id = :id: WHERE `name` = ':name:'
SQL;

        foreach ($updates as $name => $id) {
            $this->exec(strtr($sql, [':name:' => $name, ':id:' => $id]));
        }
    }

    public function getNewIssues()
    {
        $sql = <<<SQL
            SELECT issue.name, sprint.everhour_id as sprint_everhour_id FROM issue
            JOIN sprint ON issue.sprint_jira_id = sprint.jira_id
            WHERE 
              issue.publish_status = 0 AND 
              issue.status = 1 AND 
              sprint.status = 1
SQL;
        return $this->fetch($sql);
    }

    public function getUpdatedIssues()
    {
        $sql = <<<SQL
            SELECT name as name, status as status, sprint_everhour_id, everhour_id FROM issue
            WHERE issue.publish_status = 1
SQL;
        return $this->fetch($sql);
    }
}