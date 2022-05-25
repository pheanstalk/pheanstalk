<?php

declare(strict_types=1);

namespace Pheanstalk\Contract;

use Pheanstalk\Values\Job;
use Pheanstalk\Values\TubeList;
use Pheanstalk\Values\TubeName;

interface PheanstalkSubscriberInterface
{
    /**
     * Permanently deletes a job.
     */
    public function delete(JobIdInterface $job): void;

    /**
     * Remove the specified tube from the watchlist.
     * @return int The number of watched tubes for the connection
     */
    public function ignore(TubeName $tube): int;

    /**
     * The names of the tubes being watched, to reserve jobs from.
     *
     * Returns the watchlist, always queries the server
     */
    public function listTubesWatched(): TubeList;

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
    public function reserve(): Job;

    /**
     * Puts a job into a 'buried' state, revived only by 'kick' command.
     */
    public function bury(JobIdInterface $job, int $priority = PheanstalkPublisherInterface::DEFAULT_PRIORITY): void;

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
     * @param int<0, max> $timeout
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
     * @return int The number of watched tubes for the connection
     */
    public function watch(TubeName $tube): int;
}
