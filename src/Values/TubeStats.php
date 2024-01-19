<?php

declare(strict_types=1);

namespace Pheanstalk\Values;

final class TubeStats
{
    private const KEYS = [
        'name',
        'current-jobs-urgent',
        'current-jobs-ready',
        'current-jobs-reserved',
        'current-jobs-delayed',
        'current-jobs-buried',
        'total-jobs',
        'current-using',
        'current-waiting',
        'current-watching',
        'pause',
        'cmd-delete',
        'cmd-pause-tube',
        'pause-time-left'
    ];


    public function __construct(
        public readonly TubeName $name,
        public readonly int $currentJobsUrgent,
        public readonly int $currentJobsReady,
        public readonly int $currentJobsReserved,
        public readonly int $currentJobsDelayed,
        public readonly int $currentJobsBuried,
        public readonly int $totalJobs,
        public readonly int $currentUsing,
        public readonly int $currentWaiting,
        public readonly int $currentWatching,
        public readonly int $pause,
        public readonly int $cmdDelete,
        public readonly int $cmdPauseTube,
        public readonly int $pauseTimeLeft
    ) {
    }


    /**
     * @param array<string, string|int|bool|float> $data
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress PossiblyUndefinedArrayOffset
     */
    public static function fromBeanstalkArray(array $data): self
    {
        // Check that all expected keys are there
        foreach (self::KEYS as $key) {
            if (!isset($data[$key])) {
                throw new \InvalidArgumentException("Data array is missing expected key $key");
            }
        }

        $tube = new TubeName($data['name']);

        return new self(
            name: $tube,
            currentJobsUrgent: $data['current-jobs-urgent'],
            currentJobsReady: $data['current-jobs-ready'],
            currentJobsReserved: $data['current-jobs-reserved'],
            currentJobsDelayed: $data['current-jobs-delayed'],
            currentJobsBuried: $data['current-jobs-buried'],
            totalJobs: $data['total-jobs'],
            currentUsing: $data['current-using'],
            currentWaiting: $data['current-waiting'],
            currentWatching: $data['current-watching'],
            pause: $data['pause'],
            cmdDelete: $data['cmd-delete'],
            cmdPauseTube: $data['cmd-pause-tube'],
            pauseTimeLeft: $data['pause-time-left']
        );
    }
}
