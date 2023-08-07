<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\TubeName;

/**
 * The 'list-tube-used' command.
 *
 * Returns the tube currently being used by the client.
 *
 */
final class ListTubeUsedCommand implements CommandInterface
{
    public function interpret(RawResponse $response): TubeName
    {
        return match (true) {
            $response->type === ResponseType::Using && isset($response->argument) => new TubeName($response->argument),
            $response->type === ResponseType::Using => throw MalformedResponseException::expectedStringArgument(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }

    public function getCommandLine(): string
    {
        return "list-tube-used";
    }
}
