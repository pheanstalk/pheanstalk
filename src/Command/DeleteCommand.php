<?php

namespace Pheanstalk\Command;

use Pheanstalk\Exception;
use Pheanstalk\Response;

/**
 * The 'delete' command.
 * Permanently deletes an already-reserved job.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class DeleteCommand
    extends AbstractCommand
    implements \Pheanstalk\ResponseParser
{
    private $_job;

    /**
     * @param object $job Job
     */
    public function __construct($job)
    {
        $this->_job = $job;
    }

    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
    public function getCommandLine()
    {
        return 'delete '.$this->_job->getId();
    }

    /* (non-phpdoc)
     * @see ResponseParser::parseResponse()
     */
    public function parseResponse($responseLine, $responseData)
    {
        if ($responseLine == Response::RESPONSE_NOT_FOUND) {
            throw new Exception\ServerException(sprintf(
                'Cannot delete job %u: %s',
                $this->_job->getId(),
                $responseLine
            ));
        }

        return $this->_createResponse($responseLine);
    }
}
