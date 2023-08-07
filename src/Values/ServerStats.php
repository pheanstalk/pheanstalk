<?php

declare(strict_types=1);

namespace Pheanstalk\Values;

final class ServerStats
{
    private const KEYS = [
        'current-jobs-urgent',
        'current-jobs-ready',
        'current-jobs-reserved',
        'current-jobs-delayed',
        'current-jobs-buried',

        'cmd-put',
        'cmd-peek',
        'cmd-peek-ready',
        'cmd-peek-delayed',
        'cmd-peek-buried',
        'cmd-reserve',
        'cmd-use',
        'cmd-watch',
        'cmd-ignore',
        'cmd-delete',
        'cmd-release',
        'cmd-bury',
        'cmd-kick',
        'cmd-stats',

        'cmd-reserve-with-timeout',
        'cmd-stats-job',
        'cmd-stats-tube',
        'cmd-list-tubes',
        'cmd-list-tube-used',
        'cmd-list-tubes-watched',
        'cmd-pause-tube',
        'job-timeouts',
        'total-jobs',
        'max-job-size',
        'current-tubes',
        'current-connections',
        'current-producers',
        'current-workers',
        'current-waiting',
        'total-connections',
        'pid',
        'version',
        'rusage-utime',
        'rusage-stime',
        'binlog-oldest-index',
        'binlog-current-index',
        'binlog-max-size',
        'binlog-records-written',
        'draining',
        'id',
        'hostname',
        'os',
        'platform'
    ];
    public function __construct(
        public readonly int $currentJobsUrgent,
        public readonly int $currentJobsReady,
        public readonly int $currentJobsReserved,
        public readonly int $currentJobsDelayed,
        public readonly int $currentJobsBuried,
        public readonly int $cmdPut,
        public readonly int $cmdPeek,
        public readonly int $cmdPeekReady,
        public readonly int $cmdPeekDelayed,
        public readonly int $cmdReserveWithTimeout,
        public readonly int $cmdPeekBuried,
        public readonly int $cmdReserve,
        public readonly int $cmdUse,
        public readonly int $cmdWatch,
        public readonly int $cmdIgnore,
        public readonly int $cmdDelete,
        public readonly int $cmdRelease,
        public readonly int $cmdBury,
        public readonly int $cmdKick,
        public readonly int $cmdStats,
        public readonly int $cmdStatsJob,
        public readonly int $cmdStatsTube,
        public readonly int $cmdListTubes,
        public readonly int $cmdListTubeUsed,
        public readonly int $cmdListTubesWatched,
        public readonly int $cmdPauseTube,
        public readonly int $jobTimeouts,
        public readonly int $totalJobs,
        public readonly int $maxJobSize,
        public readonly int $currentTubes,
        public readonly int $currentConnections,
        public readonly int $currentProducers,
        public readonly int $currentWorkers,
        public readonly int $currentWaiting,
        public readonly int $totalConnections,
        public readonly int $pid,
        public readonly string $version,
        public readonly float $rusageUtime,
        public readonly float $rusageStime,
        public readonly int $binlogOldestIndex,
        public readonly int $binlogCurrentIndex,
        public readonly int $binlogMaxSize,
        public readonly int $binlogRecordsWritten,
        public readonly bool $draining,
        public readonly string $id,
        public readonly string $hostname,
        public readonly string $os,
        public readonly string $platform,
    ) {
    }
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
     * @psalm-suppress ArgumentTypeCoercion
     */
    public static function fromBeanstalkArray(array $data): self
    {
        // Check that all expected keys are there
        foreach (self::KEYS as $key) {
            if (!isset($data[$key])) {
                throw new \InvalidArgumentException("Data array is missing expected key $key");
            }
        }

        $params = [];
        foreach (self::KEYS as $key) {
            $params[self::camelize($key)] = $data[$key];
        }
        return new self(...$params);
    }
}
