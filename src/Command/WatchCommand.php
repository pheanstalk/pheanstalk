<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;

/**
 * The 'watch' command.
 * Adds a tube to the watchlist to reserve jobs from.
 */
final class WatchCommand extends TubeCommand
{
    public function interpret(RawResponse $response): int
    {
        if ($response->type === ResponseType::Watching && is_int($response->argument)) {
            return $response->argument;
        }
        return match ($response->type) {
            ResponseType::Watching => throw MalformedResponseException::expectedIntegerArgument(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }

    protected function getCommandTemplate(): string
    {
        return "watch {tube}";
    }
}
