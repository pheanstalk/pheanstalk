<?php
declare(strict_types=1);

namespace Pheanstalk\Contract;

use Pheanstalk\Job;

interface PheanstalkSubscriberInterface
{



    /**
     * Permanently deletes a job.
     */
    public function delete(JobIdInterface $job): void;

    /**
     * Remove the specified tube from the watchlist.
     */
    public function ignore(string $tube): void;

    /**
     * The names of the tubes being watched, to reserve jobs from.
     *
     * Returns the cached watchlist if $askServer is false (the default),
     * or queries the server for the watchlist if $askServer is true.
     *
     * @return list<string>
     */
    public function listTubesWatched(): array;

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
        int $priority = PheanstalkPublisherInterface::DEFAULT_PRIORITY,
        int $delay = PheanstalkPublisherInterface::DEFAULT_DELAY
    ): void;

    /**
     * Reserves/locks a ready job in a watched tube.
     */
    public function reserve(): ?Job;

    /**
     * Reserves/locks a specific job
     * @param JobIdInterface $job
     * @return Job
     */
    public function reserveJob(JobIdInterface $job): Job;

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
     * Add the specified tube to the watchlist, to reserve jobs from.
     * @param string $tube
     *
     */
    public function watch(string $tube): void;
}
