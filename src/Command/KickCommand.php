<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;

/**
 * The 'kick' command.
 *
 * Kicks buried or delayed jobs into a 'ready' state.
 * If there are buried jobs, it will kick up to $max of them.
 * Otherwise, it will kick up to $max delayed jobs.
 *
 */
final class KickCommand implements CommandInterface
{
    public function __construct(private readonly int $max)
    {
    }

    public function getCommandLine(): string
    {
        return "kick {$this->max}";
    }

    public function interpret(
        RawResponse $response
    ): int {
        if ($response->type === ResponseType::Kicked && is_int($response->argument)) {
            return $response->argument;
        }
        throw match ($response->type) {
            ResponseType::Kicked => MalformedResponseException::expectedIntegerArgument(),
            default => new UnsupportedResponseException($response->type),
        };
    }
}
