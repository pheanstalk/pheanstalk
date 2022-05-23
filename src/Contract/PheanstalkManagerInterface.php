<?php

declare(strict_types=1);

namespace Pheanstalk\Contract;

use Pheanstalk\Values\Job;
use Pheanstalk\Values\JobStats;
use Pheanstalk\Values\ServerStats;
use Pheanstalk\Values\TubeList;
use Pheanstalk\Values\TubeName;
use Pheanstalk\Values\TubeStats;

interface PheanstalkManagerInterface
{
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
     */
    public function listTubes(): TubeList;





    /**
     * Temporarily prevent jobs being reserved from the given tube.
     *
     * @param int    $delay Seconds before jobs may be reserved from this queue.
     */
    public function pauseTube(TubeName $tube, int $delay): void;

    /**
     * Resume jobs for a given paused tube.
     */
    public function resumeTube(TubeName $tube): void;

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
     * Gives statistical information about the specified job if it exists.
     */
    public function statsJob(JobIdInterface $job): JobStats;

    /**
     * Gives statistical information about the specified tube if it exists.
     */
    public function statsTube(TubeName $tube): TubeStats;

    /**
     * Gives statistical information about the beanstalkd system as a whole.
     */
    public function stats(): ServerStats;
}
