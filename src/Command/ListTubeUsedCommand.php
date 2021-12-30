<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\TubeName;

/**
 * The 'list-tube-used' command.
 *
 * Returns the tube currently being used by the client.
 */
final class ListTubeUsedCommand implements CommandInterface
{
    public function interpret(RawResponse $response): TubeName
    {
        if ($response->type === ResponseType::Using && is_string($response->argument)) {
            return new TubeName($response->argument);
        }

        return match ($response->type) {
            ResponseType::Using => throw MalformedResponseException::expectedStringArgument(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }

    public function getCommandLine(): string
    {
        return "list-tube-used";
    }
}
