<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\Job;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\Success;

/**
 * The 'reserve' command.
 * Reserves/locks a ready job in a watched tube.
 *
 */
final class ReserveWithTimeoutCommand implements CommandInterface
{
    /**
     * A timeout value of 0 will cause the server to immediately return either a
     * response or TIMED_OUT.  A positive value of timeout will limit the amount of
     * time the client will block on the reserve request until a job becomes
     * available.
     * @param int<0, max> $timeout
     */
    public function __construct(private readonly int $timeout)
    {
    }

    public function getCommandLine(): string
    {
        return "reserve-with-timeout {$this->timeout}";
    }

    public function interpret(RawResponse $response): Success|Job
    {
        return match (true) {
            $response->type === ResponseType::Reserved && isset($response->argument, $response->data) => new Job(new JobId($response->argument), $response->data),
            $response->type === ResponseType::Reserved => throw MalformedResponseException::expectedDataAndIntegerArgument(),
            $response->type === ResponseType::TimedOut => new Success(),
            $response->type === ResponseType::DeadlineSoon => throw new DeadlineSoonException(),

            default => throw new UnsupportedResponseException($response->type)
        };
    }
}
