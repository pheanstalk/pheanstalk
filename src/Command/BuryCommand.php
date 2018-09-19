<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Exception;

/**
 * The 'bury' command.
 *
 * Puts a job into a 'buried' state, revived only by 'kick' command.
 *
 * @author  Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class BuryCommand
    extends AbstractCommand
    implements \Pheanstalk\Contract\ResponseParserInterface
{
    private $_job;
    private $_priority;

    /**
     * @param object $job      Job
     * @param int    $priority From 0 (most urgent) to 0xFFFFFFFF (least urgent)
     */
    public function __construct($job, $priority)
    {
        $this->_job = $job;
        $this->_priority = $priority;
    }

    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
    public function getCommandLine()
    {
        return sprintf(
            'bury %u %u',
            $this->_job->getId(),
            $this->_priority
        );
    }

    /* (non-phpdoc)
     * @see ResponseParser::parseResponse()
     */
    public function parseResponse($responseLine, $responseData)
    {
        if ($responseLine == ResponseInterface::RESPONSE_NOT_FOUND) {
            throw new Exception\ServerException(sprintf(
                '%s: Job %u is not reserved or does not exist.',
                $responseLine,
                $this->_job->getId()
            ));
        } elseif ($responseLine == ResponseInterface::RESPONSE_BURIED) {
            return $this->_createResponse(ResponseInterface::RESPONSE_BURIED);
        } else {
            throw new Exception('Unhandled response: '.$responseLine);
        }
    }
}
