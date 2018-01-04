<?php
namespace Sync\Downloader;

use Monolog\Logger;
use Sync\JiraApi\AdvancedApi;

abstract class Downloader
{
    /**
     * @var AdvancedApi
     */
    protected $api;

    public function __construct($api)
    {
        $this->api = $api;
    }

    public abstract function download(Logger $logger);
}