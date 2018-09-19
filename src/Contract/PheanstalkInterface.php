<?php

namespace Pheanstalk\Contract;

use Pheanstalk\Connection;
use Pheanstalk\Job;
use Pheanstalk\Response\ArrayResponse;

interface PheanstalkInterface
{
    const DEFAULT_PORT = 11300;
    const DEFAULT_DELAY = 0; // no delay
    const DEFAULT_PRIORITY = 1024; // most urgent: 0, least urgent: 4294967295
    const DEFAULT_TTR = 60; // 1 minute
    const DEFAULT_TUBE = 'default';

    /**
     * @param Connection
     *
     * @return $this
     */
    public function setConnection(Connection $connection);

    /**
     * The internal connection object.
     * Not required for general usage.
     *
     * @return Connection
     */
    public function getConnection();

    // ----------------------------------------

    /**
     * Puts a job into a 'buried' state, revived only by 'kick' command.
     */
    public function bury(JobIdInterface $job, int $priority = self::DEFAULT_PRIORITY): void;

    /**
     * Permanently deletes a job.
     */
    public function delete(JobIdInterface $job);

    /**
     * Remove the specified tube from the watchlist.
     *
     * Does not execute an IGNORE command if the specified tube is not in the
     * cached watchlist.
     *
     * @param string $tube
     *
     * @return $this
     */
    public function ignore(string $tube): self;

    /**
     * Kicks buried or delayed jobs into a 'ready' state.
     * If there are buried jobs, it will kick up to $max of them.
     * Otherwise, it will kick up to $max delayed jobs.
     *
     * @param int $max The maximum jobs to kick
     *
     * @return int Number of jobs kicked
     */
    public function kick(int $max): int;

    /**
     * A variant of kick that operates with a single job. If the given job
     * exists and is in a buried or delayed state, it will be moved to the
     * ready queue of the the same tube where it currently belongs.
     *
     * @param Job $job Job
     *
     * @return $this
     */
    public function kickJob(JobIdInterface $job);

    /**
     * The names of all tubes on the server.
     *
     * @return string[]
     */
    public function listTubes(): array;

    /**
     * The names of the tubes being watched, to reserve jobs from.
     *
     * Returns the cached watchlist if $askServer is false (the default),
     * or queries the server for the watchlist if $askServer is true.
     *
     * @param bool $askServer
     *
     * @return string[]
     */
    public function listTubesWatched(bool $askServer = false): array;

    /**
     * The name of the current tube used for publishing jobs to.
     *
     * Returns the cached value if $askServer is false (the default),
     * or queries the server for the currently used tube if $askServer
     * is true.
     */
    public function listTubeUsed(bool $askServer = false): string;

    /**
     * Temporarily prevent jobs being reserved from the given tube.
     *
     * @param string $tube  The tube to pause
     * @param int    $delay Seconds before jobs may be reserved from this queue.
     *
     * @return $this
     */
    public function pauseTube(string $tube, int $delay): self;

    /**
     * Resume jobs for a given paused tube.
     *
     * @param string $tube The tube to resume
     *
     * @return $this
     */
    public function resumeTube(string $tube): self;

    /**
     * Inspect a job in the system, regardless of what tube it is in.
     */
    public function peek(JobIdInterface $job): Job;

    /**
     * Inspect the next ready job in the specified tube. If no tube is
     * specified, the currently used tube in used.
     */
    public function peekReady(?string $tube = null): Job;

    /**
     * Inspect the shortest-remaining-delayed job in the specified tube. If no
     * tube is specified, the currently used tube in used.
     *
     * @param string $tube
     *
     * @return object Job
     */
    public function peekDelayed(?string $tube = null): Job;

    /**
     * Inspect the next job in the list of buried jobs of the specified tube.
     * If no tube is specified, the currently used tube in used.
     */
    public function peekBuried(?string $tube = null): Job;

    /**
     * Puts a job on the queue.
     *
     * @param string $data     The job data
     * @param int    $priority From 0 (most urgent) to 0xFFFFFFFF (least urgent)
     * @param int    $delay    Seconds to wait before job becomes ready
     * @param int    $ttr      Time To Run: seconds a job can be reserved for
     *
     * @return int The new job ID
     */
    public function put(
        string $data,
        int $priority = self::DEFAULT_PRIORITY,
        int $delay = self::DEFAULT_DELAY,
        int $ttr = self::DEFAULT_TTR
    ): Job;

    /**
     * Puts a job on the queue using specified tube.
     *
     * Using this method is equivalent to calling useTube() then put(), with
     * the added benefit that it will not execute the USE command if the client
     * is already using the specified tube.
     *
     * @param string $tube     The tube to use
     * @param string $data     The job data
     * @param int    $priority From 0 (most urgent) to 0xFFFFFFFF (least urgent)
     * @param int    $delay    Seconds to wait before job becomes ready
     * @param int    $ttr      Time To Run: seconds a job can be reserved for
     *
     * @return int The new job ID
     */
    public function putInTube(
        string $tube,
        string $data,
        int $priority = self::DEFAULT_PRIORITY,
        int $delay = self::DEFAULT_DELAY,
        int $ttr = self::DEFAULT_TTR
    ): Job;

    /**
     * Puts a reserved job back into the ready queue.
     *
     * Marks the jobs state as "ready" to be run by any client.
     * It is normally used when the job fails because of a transitory error.
     *
     * @param JobIdInterface $job
     * @param int $priority From 0 (most urgent) to 0xFFFFFFFF (least urgent)
     * @param int $delay Seconds to wait before job becomes ready
     *
     * @return $this
     */
    public function release(
        JobIdInterface $job,
        int $priority = self::DEFAULT_PRIORITY,
        int $delay = self::DEFAULT_DELAY
    ): void;

    /**
     * Reserves/locks a ready job in a watched tube.
     *
     * A non-null timeout uses the 'reserve-with-timeout' instead of 'reserve'.
     *
     * A timeout value of 0 will cause the server to immediately return either a
     * response or TIMED_OUT.  A positive value of timeout will limit the amount of
     * time the client will block on the reserve request until a job becomes
     * available.
     */
    public function reserve(?int $timeout = null): ?Job;

    /**
     * Reserves/locks a ready job from the specified tube.
     *
     * A non-null timeout uses the 'reserve-with-timeout' instead of 'reserve'.
     *
     * A timeout value of 0 will cause the server to immediately return either a
     * response or TIMED_OUT.  A positive value of timeout will limit the amount of
     * time the client will block on the reserve request until a job becomes
     * available.
     *
     * Using this method is equivalent to calling watch(), ignore() then
     * reserve(), with the added benefit that it will not execute uneccessary
     * WATCH or IGNORE commands if the client is already watching the
     * specified tube.
     *
     * @return object Job
     */
    public function reserveFromTube(string $tube, ?int $timeout = null): Job;

    /**
     * Gives statistical information about the specified job if it exists.
     *
     * @param Job|int $job
     *
     * @return object
     */
    public function statsJob(JobIdInterface $job): ArrayResponse;

    /**
     * Gives statistical information about the specified tube if it exists.
     *
     * @param string $tube
     *
     * @return object
     */
    public function statsTube(string $tube): ArrayResponse;

    /**
     * Gives statistical information about the beanstalkd system as a whole.
     *
     * @return object
     */
    public function stats(): ArrayResponse;

    /**
     * Allows a worker to request more time to work on a job.
     *
     * This is useful for jobs that potentially take a long time, but you still want
     * the benefits of a TTR pulling a job away from an unresponsive worker.  A worker
     * may periodically tell the server that it's still alive and processing a job
     * (e.g. it may do this on DEADLINE_SOON).
     *
     */
    public function touch(JobIdInterface $job): void;

    /**
     * Change to the specified tube name for publishing jobs to.
     * This method would be called 'use' if it were not a PHP reserved word.
     *
     * Does not execute a USE command if the client is already using the
     * specified tube.
     *
     * @param string $tube
     *
     * @return $this
     */
    public function useTube(string $tube): self;

    /**
     * Add the specified tube to the watchlist, to reserve jobs from.
     *
     * Does not execute a WATCH command if the client is already watching the
     * specified tube.
     *
     * @param string $tube
     *
     * @return $this
     */
    public function watch(string $tube): self;

    /**
     * Adds the specified tube to the watchlist, to reserve jobs from, and
     * ignores any other tubes remaining on the watchlist.
     *
     * @param string $tube
     *
     * @return $this
     */
    public function watchOnly(string $tube): self;
}
