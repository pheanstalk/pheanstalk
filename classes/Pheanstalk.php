<?php

/**
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk
{
	const DEFAULT_PORT = 11300;
	const DEFAULT_DELAY = 0; // no delay
	const DEFAULT_PRIORITY = 0; // highest priority
	const DEFAULT_TTR = 60; // 1 minute

	private $_connection;

	/**
	 * @param string $host
	 * @param int $port
	 */
	public function __construct($host, $port = self::DEFAULT_PORT)
	{
		$this->setConnection(new Pheanstalk_Connection($host, $port));
	}

	/**
	 * @param Pheanstalk_Connection
	 */
	public function setConnection($connection)
	{
		$this->_connection = $connection;
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
		$response = $this->_connection->dispatchCommand($command);
		return $response['id'];
	}

	/**
	 * Reserves/locks a ready job in a watched tube.
	 *
	 * A timeout value of 0 will cause the server to immediately return either a
	 * response or TIMED_OUT.  A positive value of timeout will limit the amount of
	 * time the client will block on the reserve request until a job becomes
	 * available.
	 *
	 * @param int $timeout
	 * @return object Pheanstalk_Job
	 */
	public function reserve($timeout = null)
	{
		$command = new Pheanstalk_Command_ReserveCommand($timeout);
		$response = $this->_connection->dispatchCommand($command);
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
		$this->_connection->dispatchCommand($command);
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
		$this->_connection->dispatchCommand($command);
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
		$this->_connection->dispatchCommand($command);
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
		$response = $this->_connection->dispatchCommand($command);
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
		$response = $this->_connection->dispatchCommand($command);
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
		$this->_connection->dispatchCommand($command);
	}

	/**
	 * The names of the tubes being watched, to reserve jobs from.
	 *
	 * @return array
	 */
	public function getWatchedTubes()
	{
		$command = new Pheanstalk_Command_ListTubesWatchedCommand();
		$response = $this->_connection->dispatchCommand($command);
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
		$this->_connection->dispatchCommand($command);
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
		$response = $this->_connection->dispatchCommand($command);
		return $response['count'];
	}

	/**
	 * @param Pheanstalk_Job $job
	 * @return void
	 */
	public function touch($job)
	{
		$this->_connection->dispatchCommand(
			new Pheanstalk_Command_TouchCommand($job)
		);
	}
}
