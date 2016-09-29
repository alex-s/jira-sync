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
        return $this->exec($sql)->fetch_assoc();
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
            throw new Exception('No data provided');
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
            INSERT INTO sprint (`{$insertHeaders}`)
            VALUES :values
            ON DUPLICATE KEY UPDATE {$duplicates}

SQL;
        $values = [];

        $i = 0;
        foreach ($rows as $row) {
            $i++;
            $values[] = vsprintf("(" . implode(",", array_fill(0, count($keys), "'%s'")) . ")", $row);

            if (($i != 0 && $i % 2 == 0) || $i == count($rows)) {
                $sql = str_replace(':values', implode(',', $values), $sqlTemplate);

                $this->exec($sql);
                $values = [];
            }
        }
    }
}