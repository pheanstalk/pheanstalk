<?php

namespace Pheanstalk\Command;
use Pheanstalk\IResponseParser;
use Pheanstalk\IResponse;

use Pheanstalk\Exception\ServerException;

/**
 * The 'bury' command.
 * Puts a job into a 'buried' state, revived only by 'kick' command.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class BuryCommand extends AbstractCommand implements IResponseParser
{
    private $_job;
    private $_priority;

    /**
	 * @param object $job \Pheanstalk\Job
     * @param int $priority From 0 (most urgent) to 0xFFFFFFFF (least urgent)
     */
    public function __construct($job, $priority)
    {
        $this->_job = $job;
        $this->_priority = $priority;
    }

    /* (non-phpdoc)
	 * @see \Pheanstalk\ICommand::getCommandLine()
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
	 * @see \Pheanstalk\IResponseParser::parseRespose()
     */
    public function parseResponse($responseLine, $responseData)
    {
		if ($responseLine == IResponse::RESPONSE_NOT_FOUND)
		{
			throw new ServerException(sprintf(
				'%s: Job %d is not reserved or does not exist.',
				$responseLine,
				$this->_job->getId()
			));
		}
		elseif ($responseLine == IResponse::RESPONSE_BURIED)
		{
			return $this->_createResponse(IResponse::RESPONSE_BURIED);
		}
		else
		{
			throw new \Pheanstalk\Exception('Unhandled response: '.$responseLine);
        }
    }
}
