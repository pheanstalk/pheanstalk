<?php

declare(strict_types=1);

namespace Pheanstalk\Values;

class Timeout
{
    public function __construct(public readonly int $seconds, public readonly int $microSeconds)
    {
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

    public function toFloat(): float
    {
        return $this->seconds + ($this->microSeconds / 1000000);
    }
}
