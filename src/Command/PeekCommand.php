<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\Job;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\JobState;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\Success;

/**
 * The 'peek', 'peek-ready', 'peek-delayed' and 'peek-buried' commands.
 *
 * The peek commands let the client inspect a job in the system. There are four
 * variations. All but the first (peek) operate only on the currently used tube.
 *
 */
final class PeekCommand implements CommandInterface
{
    private readonly string $command;
    public function __construct(JobState $state)
    {
        $this->command = match ($state) {
            JobState::BURIED => 'peek-buried',
            JobState::DELAYED => 'peek-delayed',
            JobState::READY => 'peek-ready',
            JobState::RESERVED => throw new \InvalidArgumentException("Peeking at reserved jobs is not supported")
        };
    }

    public function getCommandLine(): string
    {
        return $this->command;
    }

    public function interpret(RawResponse $response): Job|Success
    {
        if ($response->type === ResponseType::Found && isset($response->argument) && isset($response->data)) {
            return new Job(new JobId($response->argument), $response->data);
        }
        return match ($response->type) {
            ResponseType::NotFound => new Success(),
            ResponseType::Found => throw MalformedResponseException::expectedDataAndIntegerArgument(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }
}
