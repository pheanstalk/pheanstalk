<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Exception\ClientException;

class TubeStats
{
    public readonly TubeName $name;
    public readonly int $currentJobsUrgent;
    public readonly int $currentJobsReady;
    public readonly int $currentJobsReserved;
    public readonly int $currentJobsDelayed;
    public readonly int $currentJobsBuried;

    public readonly int $totalJobs;
    public readonly int $currentUsing;
    public readonly int $currentWaiting;
    public readonly int $currentWatching;

    public readonly int $pause;
    public readonly int $cmdDelete;
    public readonly int $cmdPauseTube;
    public readonly int $cmdTimeLeft;

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
        if (!is_string($data['name'])) {
            throw new \InvalidArgumentException("Name key must contain non-empty string value");
        }

        $stats->name = new TubeName($data['name']);
        unset($data['name']);

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
