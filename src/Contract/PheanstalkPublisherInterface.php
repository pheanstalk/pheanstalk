<?php

declare(strict_types=1);

namespace Pheanstalk\Contract;

use Pheanstalk\Values\TubeName;

interface PheanstalkPublisherInterface
{
    public const DEFAULT_DELAY = 0; // no delay
    public const DEFAULT_PRIORITY = 1024; // most urgent: 0, least urgent: 4294967295
    public const DEFAULT_TTR = 60; // 1 minute

    /**
     * The name of the current tube used for publishing jobs to.
     * Always queries the server
     */
    public function listTubeUsed(): TubeName;

    /**
     * Puts a job on the queue.
     *
     * @param string $data     The job data
     * @param int    $priority From 0 (most urgent) to 0xFFFFFFFF (least urgent)
     * @param int    $delay    Seconds to wait before job becomes ready
     * @param int    $timeToRelease      Time To Run: seconds a job can be reserved for
     */
    public function put(
        string $data,
        int $priority = self::DEFAULT_PRIORITY,
        int $delay = self::DEFAULT_DELAY,
        int $timeToRelease = self::DEFAULT_TTR
    ): JobIdInterface;

    /**
     * Change to the specified tube name for publishing jobs to.
     */
    public function useTube(TubeName $tube): void;
}
