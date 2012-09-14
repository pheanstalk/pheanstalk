<?php

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
class Pheanstalk_Command_KickJobCommand
	extends Pheanstalk_Command_AbstractCommand
	implements Pheanstalk_ResponseParser
{
	private $_job;

	/**
	 * @param Pheanstalk_Job $job Pheanstalk job
	 */
	public function __construct($job)
	{
		$this->_job = $job;
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_Command::getCommandLine()
	 */
	public function getCommandLine()
	{
		return 'kick-job '.$this->_job->getId();
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_ResponseParser::parseRespose()
	 */
	public function parseResponse($responseLine, $responseData)
	{
		if ($responseLine == Pheanstalk_Response::RESPONSE_NOT_FOUND)
		{
			throw new Pheanstalk_Exception_ServerException(sprintf(
				'%s: Job %d does not exist or is not in a kickable state.',
				$responseLine,
				$this->_job->getId()
			));
		}
		elseif ($responseLine == Pheanstalk_Response::RESPONSE_KICKED)
		{
			return $this->_createResponse(Pheanstalk_Response::RESPONSE_KICKED);
		}
		else
		{
			throw new Pheanstalk_Exception('Unhandled response: '.$responseLine);
		}
	}
}
