<?php

/**
 * Pheanstalk is a pure PHP 5.2+ client for the beanstalkd workqueue.
 * The Pheanstalk class is a simple facade for the various underlying components.
 *
 * @see http://github.com/kr/beanstalkd
 * @see http://xph.us/software/beanstalkd/
 *
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
	 * @chainable
	 */
	public function setConnection($connection)
	{
		$this->_connection = $connection;
		return $this;
	}

	// ----------------------------------------

	/**
	 * Puts a job into a 'buried' state, revived only by 'kick' command.
	 *
	 * @param Pheanstalk_Job $job
	 * @return void
	 */
	public function bury($job, $priority = self::DEFAULT_PRIORITY)
	{
		$this->_dispatch(new Pheanstalk_Command_BuryCommand($job, $priority));
	}

	/**
	 * Permanently deletes a job.
	 *
	 * @param object $job Pheanstalk_Job
	 * @chainable
	 */
	public function delete($job)
	{
		$this->_dispatch(new Pheanstalk_Command_DeleteCommand($job));
		return $this;
	}

	/**
	 * Remove the specified tube from the watchlist
	 *
	 * @param string $tube
	 * @chainable
	 */
	public function ignore($tube)
	{
		$this->_dispatch(new Pheanstalk_Command_IgnoreCommand($tube));
		return $this;
	}

	/**
	 * Remove the specified tube from the watchlist
	 *
	 * @param string $tube
	 * @chainable
	 * @deprecated Pheanstalk::ignore()
	 */
	public function ignoreTube($tube)
	{
		$this->_deprecatedNotice(__FUNCTION__, 'ignore');
		return $this->ignore($tube);
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
		$response = $this->_dispatch(new Pheanstalk_Command_KickCommand($max));
		return $response['kicked'];
	}

	/**
	 * The names of all tubes on the server.
	 *
	 * @return array
	 */
	public function listTubes()
	{
		return (array) $this->_dispatch(
			new Pheanstalk_Command_ListTubesCommand()
		);
	}

	/**
	 * The names of the tubes being watched, to reserve jobs from.
	 *
	 * @return array
	 */
	public function listTubesWatched()
	{
		return (array) $this->_dispatch(
			new Pheanstalk_Command_ListTubesWatchedCommand()
		);
	}

	/**
	 * The names of the tubes being watched, to reserve jobs from.
	 *
	 * @return array
	 * @deprecated Pheanstalk::listTubesWatched()
	 */
	public function getWatchedTubes()
	{
		$this->_deprecatedNotice(__FUNCTION__, 'listTubesWatched');
		return $this->listTubesWatched();
	}

	/**
	 * The name of the current tube used for publishing jobs to.
	 *
	 * @return string
	 */
	public function listTubeUsed()
	{
		$response = $this->_dispatch(
			new Pheanstalk_Command_ListTubeUsedCommand()
		);

		return $response['tube'];
	}

	/**
	 * The name of the current tube used for publishing jobs to.
	 *
	 * @return string
	 * @deprecated Pheanstalk::listTubeUsed()
	 */
	public function getCurrentTube()
	{
		$this->_deprecatedNotice(__FUNCTION__, 'listTubeUsed');
		return $this->listTubeUsed();
	}

	/**
	 * Inspect a job in the system, regardless of what tube it is in.
	 *
	 * @param int $jobId
	 * @return object Pheanstalk_Job
	 */
	public function peek($jobId)
	{
		$response = $this->_dispatch(
			new Pheanstalk_Command_PeekCommand($jobId)
		);

		return new Pheanstalk_Job($this, $response['id'], $response['jobdata']);
	}

	/**
	 * Inspect the next ready job in the currently used tube.
	 *
	 * @return object Pheanstalk_Job
	 */
	public function peekReady()
	{
		$response = $this->_dispatch(
			new Pheanstalk_Command_PeekCommand(Pheanstalk_Command_PeekCommand::TYPE_READY)
		);

		return new Pheanstalk_Job($this, $response['id'], $response['jobdata']);
	}

	/**
	 * Inspect the shortest-remaining-delayed job in the currently used tube.
	 *
	 * @return object Pheanstalk_Job
	 */
	public function peekDelayed()
	{
		$response = $this->_dispatch(
			new Pheanstalk_Command_PeekCommand(Pheanstalk_Command_PeekCommand::TYPE_DELAYED)
		);

		return new Pheanstalk_Job($this, $response['id'], $response['jobdata']);
	}

	/**
	 * Inspect the next job in the list of buried jobs of the currently used tube.
	 *
	 * @return object Pheanstalk_Job
	 */
	public function peekBuried()
	{
		$response = $this->_dispatch(
			new Pheanstalk_Command_PeekCommand(Pheanstalk_Command_PeekCommand::TYPE_BURIED)
		);

		return new Pheanstalk_Job($this, $response['id'], $response['jobdata']);
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
		$response = $this->_dispatch(
			new Pheanstalk_Command_PutCommand($data, $priority, $delay, $ttr)
		);

		return $response['id'];
	}

	/**
	 * Puts a job into a 'buried' state, revived only by 'kick' command.
	 *
	 * @param object $job Pheanstalk_Job
	 * @param int $priority From 0 (most urgent) to 0xFFFFFFFF (least urgent)
	 * @param int $delay Seconds to wait before job becomes ready
	 * @chainable
	 */
	public function release(
		$job,
		$priority = self::DEFAULT_PRIORITY,
		$delay = self::DEFAULT_DELAY
	)
	{
		$this->_dispatch(
			new Pheanstalk_Command_ReleaseCommand($job, $priority, $delay)
		);

		return $this;
	}

	/**
	 * Reserves/locks a ready job in a watched tube.
	 *
	 * A non-null timeout uses the 'reserve-with-timeout' instead of 'reserve'.
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
		$response = $this->_dispatch(
			new Pheanstalk_Command_ReserveCommand($timeout)
		);

		return new Pheanstalk_Job($this, $response['id'], $response['jobdata']);
	}

	/**
	 * @param Pheanstalk_Job or int $job
	 * @return object
	 */
	public function statsJob($job)
	{
		return $this->_dispatch(new Pheanstalk_Command_StatsJobCommand($job));
	}

	/**
	 * @param string $tube
	 * @return object
	 */
	public function statsTube($tube)
	{
		return $this->_dispatch(new Pheanstalk_Command_StatsTubeCommand($tube));
	}

	/**
	 * @return object
	 */
	public function stats()
	{
		return $this->_dispatch(new Pheanstalk_Command_StatsCommand());
	}

	/**
	 * @param Pheanstalk_Job $job
	 * @chainable
	 */
	public function touch($job)
	{
		$this->_dispatch(new Pheanstalk_Command_TouchCommand($job));
		return $this;
	}

	/**
	 * Change to the specified tube name for publishing jobs to.
	 * This method would be called 'use' if it were not a PHP reserved word.
	 *
	 * @param string $tube
	 * @chainable
	 */
	public function useTube($tube)
	{
		$this->_dispatch(new Pheanstalk_Command_UseCommand($tube));
		return $this;
	}

	/**
	 * Add the specified tube to the watchlist, to reserve jobs from.
	 *
	 * @param string $tube
	 * @chainable
	 */
	public function watch($tube)
	{
		$this->_dispatch(new Pheanstalk_Command_WatchCommand($tube));
		return $this;
	}

	/**
	 * Add the specified tube to the watchlist, to reserve jobs from.
	 *
	 * @param string $tube
	 * @chainable
	 * @deprecated Pheanstalk::watch()
	 */
	public function watchTube($tube)
	{
		$this->_deprecatedNotice(__FUNCTION__, 'watch');
		return $this->watch($tube);
	}

	// ----------------------------------------

	/**
	 * @param Pheanstalk_Command $command
	 * @return Pheanstalk_Response
	 */
	private function _dispatch($command)
	{
		return $this->_connection->dispatchCommand($command);
	}

	/**
	 * Triggers an E_USER_NOTICE PHP error warning of method deprecation.
	 */
	private function _deprecatedNotice($oldMethod, $newMethod)
	{
		trigger_error(sprintf(
			'Pheanstalk::%s() deprecated, use Pheanstalk::%s()',
			$oldMethod,
			$newMethod
		), E_USER_NOTICE);
	}
}
