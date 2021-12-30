<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\TubeName;

/**
 * The 'use' command.
 *
 * The "use" command is for producers. Subsequent put commands will put jobs into
 * the tube specified by this command. If no use command has been issued, jobs
 * will be put into the tube named "default".
 */
final class UseCommand extends TubeCommand
{
    public function interpret(
        RawResponse $response
    ): TubeName {
        if ($response->type === ResponseType::Using && is_string($response->argument)) {
            return new TubeName($response->argument);
        }
        return match ($response->type) {
            ResponseType::Using => throw MalformedResponseException::expectedStringArgument(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }

    protected function getCommandTemplate(): string
    {
        return "use {tube}";
    }
}
