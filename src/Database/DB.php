<?php
namespace Sync\Database;


use chobie\Jira\Api\Exception;

class DB
{
    private $connection;

    private $host;
    private $user;
    private $pass;
    private $database;

    function __construct($host, $user, $pass, $database)
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

    public function mergeBuffer($table)
    {
        $sql = <<<SQL
            UPDATE {$table} as origin
            LEFT JOIN {$table}_buffer as buffer on origin.name = buffer.name
            SET origin.everhour_id = buffer.everhour_id
SQL;
        $this->exec($sql);
    }

    public function getSprintData($isAll = true)
    {
        $where = 1;

        if (!$isAll) {
            $where = 'everhour_id IS NULL';
        }
        $sql = <<<SQL
            SELECT * FROM `sprint`
            WHERE {$where}
SQL;
        return $this->fetch($sql);
    }

    public function getIssueData()
    {
        $sql = <<<SQL
            SELECT issue.*, sprint.everhour_id as sprint_everhour_id, issue.everhour_id as issue_everhour_id  FROM `issue`
            LEFT JOIN `sprint` ON issue.sprint_jira_id = sprint.jira_id
SQL;
        return $this->fetch($sql);
    }

    public function clearTable($table)
    {
        $this->exec("DELETE FROM {$table}");
    }
}