<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\NotIgnoredException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\TubeCommandTemplate;

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

        throw match ($response->type) {
            ResponseType::NotIgnored => new NotIgnoredException(),
            ResponseType::Watching => MalformedResponseException::expectedIntegerArgument(),
            default => new UnsupportedResponseException($response->type)
        };
    }

    protected function getCommandTemplate(): TubeCommandTemplate
    {
        return new TubeCommandTemplate("ignore {tube}");
    }
}
