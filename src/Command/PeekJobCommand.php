<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception;
use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Job;
use Pheanstalk\JobId;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;

/**
 * The 'peek' command.
 *
 * The peek command let the client inspect a specific job in the system.
 */
final class PeekJobCommand extends JobCommand
{
    public function interpret(RawResponse $response): Job
    {
        if ($response->type === ResponseType::Found && isset($response->argument, $response->data)) {
            return new Job(new JobId($response->argument), $response->data);
        }
        return match ($response->type) {
            ResponseType::NotFound => throw new JobNotFoundException(),
            ResponseType::Found => throw MalformedResponseException::expectedDataAndIntegerArgument(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }

    protected function getCommandTemplate(): string
    {
        return "peek {id}";
    }
}
