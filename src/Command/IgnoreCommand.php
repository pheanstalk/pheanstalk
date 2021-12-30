<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception;
use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\NotIgnoredException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;

/**
 * The 'ignore' command.
 * Removes a tube from the watch list to reserve jobs from.
 */
final class IgnoreCommand extends TubeCommand
{
    public function interpret(RawResponse $response): int
    {
        if ($response->type === ResponseType::Watching && is_int($response->argument)) {
            return $response->argument;
        }

        return match ($response->type) {
            ResponseType::NotIgnored => throw new NotIgnoredException(),
            ResponseType::Watching => throw MalformedResponseException::expectedIntegerArgument(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }

    protected function getCommandTemplate(): string
    {
        return "ignore {tube}";
    }
}
