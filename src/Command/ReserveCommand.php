<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\TimedOutException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Job;
use Pheanstalk\JobId;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;

/**
 * The 'reserve' command.
 *
 * Reserves/locks a ready job in a watched tube.
 */
final class ReserveCommand implements CommandInterface
{
    public function getCommandLine(): string
    {
        return 'reserve';
    }

    public function interpret(RawResponse $response): Job
    {
        if ($response->type === ResponseType::Reserved && isset($response->argument) && isset($response->data)) {
            return new Job($response->argument, $response->data);
        }
        return match ($response->type) {
            ResponseType::DeadlineSoon => throw new DeadlineSoonException(),
            ResponseType::TimedOut => throw new TimedOutException(),
            ResponseType::Reserved => throw MalformedResponseException::expectedDataAndIntegerArgument(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }
}
