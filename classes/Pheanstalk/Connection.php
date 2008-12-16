<?php

/**
 * A connection to a beanstalkd server
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Connection
{

	const CONNECT_TIMEOUT = 2;
	const CRLF = "\r\n";
	const CRLF_LENGTH = 2;

	const DEFAULT_DELAY = 0; // no delay
	const DEFAULT_PRIORITY = 0; // highest priority
	const DEFAULT_TTR = 60; // 1 minute

	// responses which are global errors
	private $_errorResponses = array(
		Pheanstalk_Response::RESPONSE_OUT_OF_MEMORY,
		Pheanstalk_Response::RESPONSE_INTERNAL_ERROR,
		Pheanstalk_Response::RESPONSE_DRAINING,
		Pheanstalk_Response::RESPONSE_BAD_FORMAT,
		Pheanstalk_Response::RESPONSE_UNKNOWN_COMMAND,
	);

	// responses which are followed by data
	private $_dataResponses = array(
		Pheanstalk_Response::RESPONSE_RESERVED,
		Pheanstalk_Response::RESPONSE_FOUND,
		Pheanstalk_Response::RESPONSE_OK,
	);

	/**
	 * The socket file pointer for the connection.
	 *
	 * @var resource $_socket
	 */
	private $_socket;

	/**
	 * @param string $hostname
	 * @param int $port
	 * @throws Pheanstalk_Exception_ClientException
	 */
	public function __construct($hostname, $port)
	{
		if (!$this->_socket = @fsockopen($hostname, $port, $errno, $errstr, self::CONNECT_TIMEOUT))
		{
			throw new Pheanstalk_Exception_ConnectionException($errno, $errstr);
		}

		// prevent timeouts on the socket, hopefully?
		stream_set_timeout($this->_socket,-1);
	}

	/**
	 * Puts a job on the queue.
	 *
	 * @param string $data The job data
	 * @param int $priority From 0 (most urgent) to 0xFFFFFFFF (least urgent)
	 * @param int $delay Seconds to wait before job becomes ready
	 * @param int $ttr Time To Run: seconds a job can be reserved for
	 * @return int The new job ID
	 */
	public function put(
		$data,
		$priority = self::DEFAULT_PRIORITY,
		$delay = self::DEFAULT_DELAY,
		$ttr = self::DEFAULT_TTR
	)
	{
		$command = new Pheanstalk_Command_PutCommand($data, $priority, $delay, $ttr);
		$response = $this->_sendCommand($command, $command);
		return $response['id'];
	}

	/**
	 * Reserves/locks a ready job in a watched tube.
	 *
	 * @return object Pheanstalk_Job
	 */
	public function reserve()
	{
		$command = new Pheanstalk_Command_ReserveCommand();
		$response = $this->_sendCommand($command, $command);
		return new Pheanstalk_Job($this, $response['id'], $response['jobdata']);
	}

	/**
	 * Puts a job into a 'buried' state, revived only by 'kick' command.
	 *
	 * @param object $job Pheanstalk_Job
	 * @param int $priority From 0 (most urgent) to 0xFFFFFFFF (least urgent)
	 * @param int $delay Seconds to wait before job becomes ready
	 * @return void
	 */
	public function release(
		$job,
		$priority = self::DEFAULT_PRIORITY,
		$delay = self::DEFAULT_DELAY
	)
	{
		$command = new Pheanstalk_Command_ReleaseCommand($job, $priority, $delay);
		$this->_sendCommand($command, $command);
	}

	/**
	 * Permanently deletes an already-reserved job.
	 *
	 * @param object $job Pheanstalk_Job
	 * @return void
	 */
	public function delete($job)
	{
		$command = new Pheanstalk_Command_DeleteCommand($job);
		$this->_sendCommand($command, $command);
	}

	/**
	 * Puts a job into a 'buried' state, revived only by 'kick' command.
	 *
	 * @param object $job Pheanstalk_Job
	 * @return void
	 */
	public function bury($job, $priority = self::DEFAULT_PRIORITY)
	{
		$command = new Pheanstalk_Command_BuryCommand($job, $priority);
		$this->_sendCommand($command, $command);
	}

	/**
	 * Kicks buried or delayed jobs into a 'ready' state.
	 * If there are buried jobs, it will kick up to $max of them.
	 * Otherwise, it will kick up to $max delayed jobs.
	 *
	 * @param int $max The maximum jobs to kick
	 * @return int Number of jobs kicked
	 */
	public function kick($max)
	{
		$command = new Pheanstalk_Command_KickCommand($max);
		$response = $this->_sendCommand($command, $command);
		return $response['kicked'];
	}

	/**
	 * The name of the current tube used for publishing jobs to
	 *
	 * @return string
	 */
	public function getCurrentTube()
	{
		$command = new Pheanstalk_Command_ListTubeUsedCommand();
		$response = $this->_sendCommand($command, $command);
		return $response['tube'];
	}

	/**
	 * Change to the specified tube name for publishing jobs to
	 *
	 * @param string $tube
	 * @return void
	 */
	public function useTube($tube)
	{
		$command = new Pheanstalk_Command_UseCommand($tube);
		$this->_sendCommand($command, $command);
	}

	/**
	 * The names of the tubes being watched, to reserve jobs from.
	 *
	 * @return array
	 */
	public function getWatchedTubes()
	{
		$command = new Pheanstalk_Command_ListTubesWatchedCommand();
		$response = $this->_sendCommand($command, $command);
		return $response['tubes'];
	}

	/**
	 * Add the specified tube to the watchlist, to reserve jobs from.
	 *
	 * @param string $tube
	 * @return void
	 */
	public function watchTube($tube)
	{
		$command = new Pheanstalk_Command_WatchCommand($tube);
		$this->_sendCommand($command, $command);
	}

	/**
	 * Remove the specified tube from the watchlist
	 *
	 * @param string $tube
	 * @return void
	 */
	public function ignoreTube($tube)
	{
		$command = new Pheanstalk_Command_IgnoreCommand($tube);
		$response = $this->_sendCommand($command, $command);
		return $response['count'];
	}

	// ----------------------------------------

	/**
	 * @param object $command Pheanstalk_Command
	 * @param object $parser Pheanstalk_ResponseParser
	 * @return object Pheanstalk_Response
	 */
	private function _sendCommand($command, $responseParser)
	{
		fwrite($this->_socket, $command->getCommandLine().self::CRLF);

		if ($command->hasData())
		{
			fwrite($this->_socket, $command->getData().self::CRLF);
		}

		$responseLine = rtrim(fgets($this->_socket));
		$responseName = preg_replace('#^(\S+).*$#s', '$1', $responseLine);

		if (in_array($responseName, $this->_errorResponses))
		{
			// TODO: throw correctly typed exception
			throw new Pheanstalk_Exception_ServerException(sprintf(
				"%s in response to '%s'",
				$responseName,
				$command
			));
		}

		if (in_array($responseName, $this->_dataResponses))
		{
			$dataLength = preg_replace('#^.*\b(\d+)$#', '$1', $responseLine);
			$data = fread($this->_socket, $dataLength);

			$crlf = fread($this->_socket, self::CRLF_LENGTH);
			if ($crlf !== self::CRLF)
			{
				throw new Pheanstalk_Exception_ClientException(sprintf(
					'Expected %d bytes of CRLF after %d bytes of data',
					self::CRLF_LENGTH,
					$dataLength
				));
			}
		}
		else
		{
			$data = null;
		}

		return $command->parseResponse($responseLine, $data);
	}

}

?>
