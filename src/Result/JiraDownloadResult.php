<?php
namespace Sync\Result;


class JiraDownloadResult
{
    private $sprints;
    private $issues;

    public function __construct($sprints, $issues)
    {
        $this->issues = $issues;
        $this->sprints = $sprints;
    }

    public function __toString()
    {
        return sprintf(' - downloaded %d sprints and %d issues', count($this->sprints), count($this->issues));
    }

    /**
     * @return array
     */
    public function getSprints()
    {
        return $this->sprints;
    }

    /**
     * @return array
     */
    public function getIssues()
    {
        return $this->issues;
    }
}