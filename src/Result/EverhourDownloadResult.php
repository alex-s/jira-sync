<?php
namespace Sync\Result;


class EverhourDownloadResult
{
    private $sections;
    private $issues;
    private $users;

    public function __construct($sections, $issues, $users)
    {
        $this->issues = $issues;
        $this->sections = $sections;
        $this->users = $users;
    }

    public function __toString()
    {
        return sprintf(' - downloaded %d sections, %d issues and %d users', count($this->sections), count($this->issues), count($this->users));
    }

    /**
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @return array
     */
    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }
}