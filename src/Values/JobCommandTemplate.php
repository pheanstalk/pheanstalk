<?php

declare(strict_types=1);

namespace Pheanstalk\Values;

use Pheanstalk\Contract\JobIdInterface;

/**
 * Simple value object that has a very basic render function
 */
final class JobCommandTemplate
{
    private const PLACEHOLDER = '{id}';
    private string $value;

    public function __construct(string $value)
    {
        if (!str_contains($value, self::PLACEHOLDER)) {
            throw new \InvalidArgumentException("Job command template must contain job id placeholder");
        }
        $this->value = $value;
    }

    public function render(JobIdInterface $jobId): string
    {
        return strtr($this->value, [self::PLACEHOLDER => $jobId->getId()]);
    }
}
