<?php

declare(strict_types=1);

namespace Pheanstalk\Values;

final class JobStats
{
    private const KEYS = [
        'id',
        'tube',
        'state',
        'pri',
        'age',
        'delay',
        'ttr',
        'time-left',
        'file',
        'reserves',
        'timeouts',
        'releases',
        'buries',
        'kicks'
    ];

    public function __construct(
        public readonly JobId $id,
        public readonly TubeName $tube,
        public readonly JobState $state,
        public readonly int $priority,
        public readonly int $age,
        public readonly int $delay,
        public readonly int $timeToRelease,
        public readonly int $timeLeft,
        public readonly int $file,
        public readonly int $reserves,
        public readonly int $timeouts,
        public readonly int $releases,
        public readonly int $buries,
        public readonly int $kicks
    ) {
    }

    /**
     * @param array<string, scalar> $data
     * @throws \InvalidArgumentException
     * @psalm-suppress PossiblyUndefinedArrayOffset
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress PossiblyInvalidArgument
     */
    public static function fromBeanstalkArray(array $data): self
    {
        // Check that all expected keys are there
        foreach (self::KEYS as $key) {
            if (!isset($data[$key])) {
                throw new \InvalidArgumentException("Data array is missing expected key $key");
            }
        }

        $tube = new TubeName($data['tube']);

        try {
            $state = JobState::from($data['state']);
        } catch (\Throwable $t) {
            throw new \InvalidArgumentException("Invalid value given for state", 0, $t);
        }

        $id = new JobId($data['id']);

        // WSL2/Beanstalkd compatibility: Cast int fields to ensure strict typing (handles float drift like -1.0 → -1)
        $priority = (int) $data['pri'];
        $age = (int) $data['age'];
        $delay = (int) $data['delay'];
        $timeToRelease = (int) $data['ttr'];
        $timeLeft = (int) $data['time-left'];
        $file = (int) $data['file'];
        $reserves = (int) $data['reserves'];
        $timeouts = (int) $data['timeouts'];
        $releases = (int) $data['releases'];
        $buries = (int) $data['buries'];
        $kicks = (int) $data['kicks'];

        return new self(
            id: $id,
            tube: $tube,
            state: $state,
            priority: $priority,
            age: $age,
            delay: $delay,
            timeToRelease: $timeToRelease,
            timeLeft: $timeLeft,
            file: $file,
            reserves: $reserves,
            timeouts: $timeouts,
            releases: $releases,
            buries: $buries,
            kicks: $kicks
        );
    }
}
