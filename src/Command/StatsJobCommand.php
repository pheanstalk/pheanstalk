<?php

namespace Pheanstalk\Command;

use Pheanstalk\YamlResponseParser;

/**
 * The 'stats-job' command.
 * Gives statistical information about the specified job if it exists.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class StatsJobCommand
    extends AbstractCommand
{
    private $_jobId;

    /**
     * @param Job|int $job
     */
    public function __construct($job)
    {
        $this->_jobId = is_object($job) ? $job->getId() : $job;
    }

    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
    public function getCommandLine()
    {
        return sprintf('stats-job %u', $this->_jobId);
    }

    /* (non-phpdoc)
     * @see Command::getResponseParser()
     */
    public function getResponseParser()
    {
        return new YamlResponseParser(
            YamlResponseParser::MODE_DICT
        );
    }
}
