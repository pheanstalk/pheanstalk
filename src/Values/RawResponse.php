<?php

declare(strict_types=1);

namespace Pheanstalk\Values;

/**
 * Wraps the parts of a response to a beanstalkd command
 */
final class RawResponse
{
    public readonly int|string|null $argument;

    public function __construct(
        public readonly ResponseType $type,
        string|null $argument = null,
        public readonly null|string $data = null
    ) {
        // Cast numeric strings to integers, if possible.
        $this->argument = match (true) {
            $argument === null => null,
            ctype_digit($argument) && (!str_starts_with($argument, "0") || $argument === '0') && $argument < PHP_INT_MAX => (int)$argument,
            default => $argument
        };
    }
}
