<?php
namespace Sync\Downloader;

use Sync\Database\DB;
use Sync\JiraApi\AdvancedApi;

abstract class Downloader
{
    /**
     * @var DB
     */
    protected $db;

    /**
     * @var AdvancedApi
     */
    protected $api;

    function __construct($db, $api)
    {
        $this->db = $db;
        $this->api = $api;
    }

    public abstract function download($isKanban = false);
}