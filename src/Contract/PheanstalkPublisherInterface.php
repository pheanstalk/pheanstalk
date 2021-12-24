<?php
declare(strict_types=1);

namespace Pheanstalk\Contract;

use Pheanstalk\Job;

interface PheanstalkPublisherInterface
{
    public const DEFAULT_DELAY = 0; // no delay
    public const DEFAULT_PRIORITY = 1024; // most urgent: 0, least urgent: 4294967295
    public const DEFAULT_TTR = 60; // 1 minute

    /**
     * Puts a job into a 'buried' state, revived only by 'kick' command.
     */
    public function bury(JobIdInterface $job, int $priority = self::DEFAULT_PRIORITY): void;

    /**
     * The name of the current tube used for publishing jobs to.
     *
     * Returns the cached value if $askServer is false (the default),
     * or queries the server for the currently used tube if $askServer
     * is true.
     */
    public function listTubeUsed(): string;

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
     * Change to the specified tube name for publishing jobs to.
     */
    public function useTube(string $tube);

}
