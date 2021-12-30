<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Exception\ClientException;

class JobStats
{
    public readonly JobId $id;
    public readonly TubeName $tube;
    public readonly JobState $state;
    public readonly int $pri;
    public readonly int $age;
    public readonly int $delay;

    public readonly int $ttr;
    public readonly int $timeLeft;
    public readonly int $file;
    public readonly int $reserves;
    public readonly int $timeouts;
    public readonly int $releases;
    public readonly int $buries;
    public readonly int $kicks;

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
        if (!is_string($data['tube'])) {
            throw new \InvalidArgumentException("Tube key must contain non-empty string value");
        }
        $stats->tube = new TubeName($data['tube']);

        if (!is_string($data['state'])) {
            throw new \InvalidArgumentException("State key must contain non-empty string value");
        }
        $stats->state = JobState::from($data['state']);

        if (!is_string($data['id']) && !is_int($data['id'])) {
            throw new \InvalidArgumentException("Id key must contain string or int value");
        }
        $stats->id = new JobId($data['id']);

        unset($data['id'], $data['tube'], $data['state']);

        foreach ($data as $key => $value) {
            $property = self::camelize($key);
            try {
                $stats->$property = $value;
                /** @phpstan-ignore-next-line https://github.com/phpstan/phpstan/issues/6256 */
            } catch (\TypeError $e) {
                throw new ClientException("Failed to assign value {$value} to property {$property}", 0, $e);
            }
        }
        return $stats;
    }
}
