<?php

namespace Pheanstalk\Command;

use Pheanstalk\Exception;
use Pheanstalk\Response;

/**
 * The 'release' command.
 * Releases a reserved job back onto the ready queue.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class ReleaseCommand
    extends AbstractCommand
    implements \Pheanstalk\ResponseParser
{
    private $_job;
    private $_priority;
    private $_delay;

    /**
     * @param object $job      Job
     * @param int    $priority From 0 (most urgent) to 0xFFFFFFFF (least urgent)
     * @param int    $delay    Seconds to wait before job becomes ready
     */
    public function __construct($job, $priority, $delay)
    {
        $this->_job = $job;
        $this->_priority = $priority;
        $this->_delay = $delay;
    }

    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
    public function getCommandLine()
    {
        return sprintf(
            'release %u %u %u',
            $this->_job->getId(),
            $this->_priority,
            $this->_delay
        );
    }

    /* (non-phpdoc)
     * @see ResponseParser::parseResponse()
     */
    public function parseResponse($responseLine, $responseData)
    {
        if ($responseLine == Response::RESPONSE_BURIED) {
            throw new Exception\ServerException(sprintf(
                'Job %u %s: out of memory trying to grow data structure',
                $this->_job->getId(),
                $responseLine
            ));
        }

        if ($responseLine == Response::RESPONSE_NOT_FOUND) {
            throw new Exception\ServerException(sprintf(
                'Job %u %s: does not exist or is not reserved by client',
                $this->_job->getId(),
                $responseLine
            ));
        }

        return $this->_createResponse($responseLine);
    }
}
