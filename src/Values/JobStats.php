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
        return new self(
            id: $id,
            tube: $tube,
            state: $state,
            priority: $data['pri'],
            age: $data['age'],
            delay: $data['delay'],
            timeToRelease: $data['ttr'],
            timeLeft: $data['time-left'],
            file: $data['file'],
            reserves: $data['reserves'],
            timeouts: $data['timeouts'],
            releases: $data['releases'],
            buries: $data['buries'],
            kicks: $data['kicks']
        );
    }
}
