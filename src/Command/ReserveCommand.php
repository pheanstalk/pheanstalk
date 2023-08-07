<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\TimedOutException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\Job;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;

/**
 * The 'reserve' command.
 *
 * Reserves/locks a ready job in a watched tube.
 *
 */
final class ReserveCommand implements CommandInterface
{
    public function getCommandLine(): string
    {
        return 'reserve';
    }

    public function interpret(RawResponse $response): Job
    {
        return match (true) {
            $response->type === ResponseType::Reserved && isset($response->argument, $response->data) => new Job(new JobId($response->argument), $response->data),
            $response->type === ResponseType::Reserved => throw MalformedResponseException::expectedDataAndIntegerArgument(),
            $response->type === ResponseType::DeadlineSoon => throw new DeadlineSoonException(),
            $response->type === ResponseType::TimedOut => throw new TimedOutException(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }
}
