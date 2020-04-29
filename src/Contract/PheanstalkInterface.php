<?php

namespace Pheanstalk\Contract;

use Pheanstalk\Job;

interface PheanstalkInterface
{
    const DEFAULT_PORT = 11300;
    const DEFAULT_DELAY = 0; // no delay
    const DEFAULT_PRIORITY = 1024; // most urgent: 0, least urgent: 4294967295
    const DEFAULT_TTR = 60; // 1 minute
    const DEFAULT_TUBE = 'default';

    // ----------------------------------------

    /**
     * Puts a job into a 'buried' state, revived only by 'kick' command.
     */
    public function bury(JobIdInterface $job, int $priority = self::DEFAULT_PRIORITY): void;

    /**
     * Permanently deletes a job.
     */
    public function delete(JobIdInterface $job): void;

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
     */
    public function kickJob(JobIdInterface $job): void;

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
     */
    public function pauseTube(string $tube, int $delay): void;

    /**
     * Resume jobs for a given paused tube.
     * @param string $tube The tube to resume
     */
    public function resumeTube(string $tube): void;

    /**
     * Inspect a job in the system, regardless of what tube it is in.
     */
    public function peek(JobIdInterface $job): Job;

    /**
     * Inspect the next ready job in the currently used tube.
     */
    public function peekReady(): ?Job;

    /**
     * Inspect the shortest-remaining-delayed job in the currently used tube.
     * @return ?Job
     */
    public function peekDelayed(): ?Job;

    /**
     * Inspect the next job in the list of buried jobs in the currently used tube.
     */
    public function peekBuried(): ?Job;

    /**
     * Puts a job on the queue.
     *
     * @param string $data     The job data
     * @param int    $priority From 0 (most urgent) to 0xFFFFFFFF (least urgent)
     * @param int    $delay    Seconds to wait before job becomes ready
     * @param int    $ttr      Time To Run: seconds a job can be reserved for
     */
    public function put(
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
     */
    public function release(
        JobIdInterface $job,
        int $priority = self::DEFAULT_PRIORITY,
        int $delay = self::DEFAULT_DELAY
    ): void;

    /**
     * Reserves/locks a ready job in a watched tube.
     */
    public function reserve(): ?Job;

    /**
     * Reserves/locks a ready job in a watched tube, uses the 'reserve-with-timeout' instead of 'reserve'.
     *
     * A timeout value of 0 will cause the server to immediately return either a
     * response or TIMED_OUT.  A positive value of timeout will limit the amount of
     * time the client will block on the reserve request until a job becomes
     * available.
     */
    public function reserveWithTimeout(int $timeout): ?Job;

    /**
     * Gives statistical information about the specified job if it exists.
     */
    public function statsJob(JobIdInterface $job): ResponseInterface;

    /**
     * Gives statistical information about the specified tube if it exists.
     */
    public function statsTube(string $tube): ResponseInterface;

    /**
     * Gives statistical information about the beanstalkd system as a whole.
     */
    public function stats(): ResponseInterface;

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
