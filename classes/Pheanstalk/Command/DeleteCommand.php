<?php

namespace Pheanstalk\Command;
use Pheanstalk\IResponseParser;
use Pheanstalk\IResponse;

use Pheanstalk\Exception\ServerException;

/**
 * The 'delete' command.
 * Permanently deletes an already-reserved job.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class DeleteCommand extends AbstractCommand implements IResponseParser
{
	private $_job;

	/**
	 * @param object $job \Pheanstalk\Job
	 */
	public function __construct($job)
	{
		$this->_job = $job;
	}

	/* (non-phpdoc)
	 * @see \Pheanstalk\ICommand::getCommandLine()
	 */
	public function getCommandLine()
	{
		return 'delete '.$this->_job->getId();
	}

	/* (non-phpdoc)
	 * @see \Pheanstalk\IResponseParser::parseRespose()
	 */
	public function parseResponse($responseLine, $responseData)
	{
		if ($responseLine == IResponse::RESPONSE_NOT_FOUND)
		{
			throw new ServerException(sprintf(
				'Cannot delete job %d: %s',
				$this->_job->getId(),
				$responseLine
			));
		}

		return $this->_createResponse($responseLine);
	}
}
