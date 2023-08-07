<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\TubeCommandTemplate;
use Pheanstalk\Values\TubeName;

/**
 * The 'use' command.
 *
 * The "use" command is for producers. Subsequent put commands will put jobs into
 * the tube specified by this command. If no use command has been issued, jobs
 * will be put into the tube named "default".
 *
 */
final class UseCommand extends TubeCommand
{
    public function interpret(
        RawResponse $response
    ): TubeName {
        return match (true) {
            $response->type === ResponseType::Using && isset($response->argument) => new TubeName(is_int($response->argument) ? (string) $response->argument : $response->argument),
            $response->type === ResponseType::Using => throw MalformedResponseException::expectedIntegerArgument(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }

    protected function getCommandTemplate(): TubeCommandTemplate
    {
        return new TubeCommandTemplate("use {tube}");
    }
}
