<?php

declare(strict_types=1);


namespace Pheanstalk\Values;

use Pheanstalk\Contract\JobIdInterface;

/**
 * This class implements a value object for beanstalkd job IDs.
 */
final class JobId implements JobIdInterface
{
    private readonly string $id;

    public function __construct(int|string|JobIdInterface $id)
    {
        $this->id = match (true) {
            $id instanceof JobIdInterface => $id->getId(),
            is_string($id) && ctype_digit($id) => $id,
            is_int($id) && $id >= 0 => (string) $id,
            default => throw new \InvalidArgumentException('Id must a numeric string or an integer with value >= 0')
        };
    }

    public function getId(): string
    {
        return $this->id;
    }
}
