<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Exception\ClientException;

class ServerStats
{
    public readonly int $currentJobsUrgent;
    public readonly int $currentJobsReady;
    public readonly int $currentJobsReserved;
    public readonly int $currentJobsDelayed;
    public readonly int $currentJobsBuried;

    public readonly int $cmdPut;
    public readonly int $cmdPeek;
    public readonly int $cmdPeekReady;
    public readonly int $cmdPeekDelayed;
    public readonly int $cmdPeekBuried;
    public readonly int $cmdReserve;
    public readonly int $cmdUse;
    public readonly int $cmdWatch;
    public readonly int $cmdIgnore;
    public readonly int $cmdDelete;
    public readonly int $cmdRelease;
    public readonly int $cmdBury;
    public readonly int $cmdKick;
    public readonly int $cmdStats;
    public readonly int $cmdStatsJob;
    public readonly int $cmdStatsTube;
    public readonly int $cmdListTubes;
    public readonly int $cmdListTubeUsed;
    public readonly int $cmdListTubesWatched;
    public readonly int $cmdPauseTube;
    public readonly int $jobTimeouts;
    public readonly int $totalJobs;
    public readonly int $maxJobSize;
    public readonly int $currentTubes;
    public readonly int $currentConnections;
    public readonly int $currentProducers;
    public readonly int $currentWorkers;
    public readonly int $currentWaiting;
    public readonly int $totalConnections;
    public readonly int $pid;
    public readonly string $version;
    public readonly float $rusageUtime;
    public readonly float $rusageStime;
    public readonly int $binlogOldestIndex;
    public readonly int $binlogCurrentIndex;
    public readonly int $binlogMaxSize;
    public readonly int $binlogRecordsWritten;
    public readonly bool $draining;
    public readonly string $id;
    public readonly string $hostname;
    public readonly string $os;
    public readonly string $platform;

    private static function camelize(string $key): string
    {
        $parts = explode('-', $key);
        $result = array_shift($parts);

        foreach ($parts as $part) {
            $result .= ucfirst($part);
        }
        return $result;
    }

    /**
     * @param array<string, string|int|bool|float> $data
     */
    public static function fromBeanstalkArray(array $data): self
    {
        $stats = new self();
        foreach ($data as $key => $value) {
            $property = self::camelize($key);
            try {
                $stats->$property = $value;
                /** @phpstan-ignore-next-line https://github.com/phpstan/phpstan/issues/6256 */
            } catch (\TypeError $e) {
                throw new ClientException("Failed to assign value {$value} to property {$property}", 0, $e);
            }
        }

        if (!isset($data['version'])) {
            $stats->version = "";
        }
        return $stats;
    }
}
