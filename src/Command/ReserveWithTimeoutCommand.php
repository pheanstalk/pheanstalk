<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Job;
use Pheanstalk\JobId;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\Success;

/**
 * The 'reserve' command.
 * Reserves/locks a ready job in a watched tube.
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
        if ($response->type === ResponseType::Reserved && isset($response->argument) && isset($response->data)) {
            return new Job($response->argument, $response->data);
        }
        return match ($response->type) {
            ResponseType::TimedOut => new Success(),
            ResponseType::DeadlineSoon => throw new DeadlineSoonException(),
            ResponseType::Reserved => throw MalformedResponseException::expectedDataAndIntegerArgument(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }
}
