<?php

declare(strict_types=1);


namespace Pheanstalk;

use Pheanstalk\Contract\JobIdInterface;

/**
 * This class implements a value object for beanstalkd job IDs.
 */
class JobId implements JobIdInterface
{
    private readonly string $id;

    public function __construct(int|string|JobIdInterface $id)
    {
        if ($id instanceof JobIdInterface) {
            $this->id = $id->getId();
            return;
        }

        if (is_string($id) && preg_match('/^\d+$/', $id) !== 1
            || is_int($id) && $id < 0
        ) {
            throw new \InvalidArgumentException('Id must a numeric string or an integer with value >= 0');
        }
        $this->id = (string)$id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
