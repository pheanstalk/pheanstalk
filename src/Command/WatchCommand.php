<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\TubeCommandTemplate;

/**
 * The 'watch' command.
 * Adds a tube to the watchlist to reserve jobs from.
 *
 * @internal
 */
final class WatchCommand extends TubeCommand
{
    /**
     * @param RawResponse $response
     * @return int The number of tubes currently in the watch list
     * @throws MalformedResponseException
     * @throws UnsupportedResponseException
     */
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

    protected function getCommandTemplate(): TubeCommandTemplate
    {
        return new TubeCommandTemplate("watch {tube}");
    }
}
