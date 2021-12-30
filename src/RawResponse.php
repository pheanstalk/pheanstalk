<?php

declare(strict_types=1);

namespace Pheanstalk;

class RawResponse
{
    public readonly int|string|null $argument;

    public function __construct(
        public readonly ResponseType $type,
        string|null $argument = null,
        public readonly null|string $data = null
    ) {
        // Cast numeric strings to integers, if possible
        $this->argument =
            $argument !== null
            && $this->type !== ResponseType::Using
            && ctype_digit($argument)
            && !str_starts_with($argument, "0")
            && $argument < PHP_INT_MAX
            ? (int) $argument
                : $argument;
    }
}
