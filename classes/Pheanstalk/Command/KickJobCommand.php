<?php

namespace Pheanstalk\Command;

use Pheanstalk\IResponseParser;
use Pheanstalk\Response;
use Pheanstalk\IResponse;
use Pheanstalk\Exception;
use Pheanstalk\Job;

/**
 * The 'kick-job' command.
 * Kicks a specific buried or delayed job into a 'ready' state.
 *
 * A variant of kick that operates with a single job. If the given job
 * exists and is in a buried or delayed state, it will be moved to the
 * ready queue of the the same tube where it currently belongs.
 *
 * @author Matthieu Napoli
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class KickJobCommand
	extends AbstractCommand
	implements IResponseParser
{
	private $_job;

	/**
	 * @param Job $job Pheanstalk job
	 */
	public function __construct(Job $job)
	{
		$this->_job = $job;
	}

	/* (non-phpdoc)
	 * @see Pheanstalk\ICommand::getCommandLine()
	 */
	public function getCommandLine()
	{
		return 'kick-job '.$this->_job->getId();
	}

	/* (non-phpdoc)
	 * @see IResponseParser::parseRespose()
	 */
	public function parseResponse($responseLine, $responseData)
	{
		if ($responseLine == IResponse::RESPONSE_NOT_FOUND)
		{
			throw new Exception\ServerException(sprintf(
				'%s: Job %d does not exist or is not in a kickable state.',
				$responseLine,
				$this->_job->getId()
			));
		}
		elseif ($responseLine == IResponse::RESPONSE_KICKED)
		{
			return $this->_createResponse(IResponse::RESPONSE_KICKED);
		}
		else
		{
			throw new Exception('Unhandled response: '.$responseLine);
		}
	}
}
