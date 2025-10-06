<?php

declare(strict_types=1);

namespace Pheanstalk\Values;

final class Timeout
{
    public readonly int $seconds;
    public readonly int $microSeconds;

    public function __construct(int $seconds, int $microSeconds = 0)
    {
        $this->seconds = match (true) {
            $seconds >= 0 => $seconds,
            default => throw new \InvalidArgumentException('seconds value must be >= 0')
        };
        $this->microSeconds = match (true) {
            $microSeconds >= 0 => $microSeconds,
            default => throw new \InvalidArgumentException('microSeconds value must be >= 0')
        };
    }

    /**
     * @return array{sec: int, usec: int} Array for usage in socket functions
     */
    public function toArray(): array
    {
        return [
            'sec' => $this->seconds,
            'usec' => $this->microSeconds
        ];
    }

    /**
     * @param Timeout|null $other
     * @return static
     */
    public function add(?Timeout $other = null): static
    {
        if ($other === null) {
            return clone $this;
        }

        return new static(
            $other->seconds + $this->seconds + intval(($other->microSeconds + $this->microSeconds) / 1000000),
            ($other->microSeconds + $this->microSeconds) % 1000000
        );
    }

    public function toFloat(): float
    {
        return $this->seconds + ($this->microSeconds / 1000000);
    }
}
