<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\Job;
use Pheanstalk\Values\JobCommandTemplate;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;

/**
 * The 'peek' command.
 *
 * The peek command let the client inspect a specific job in the system.
 *
 */
final class PeekJobCommand extends JobCommand
{
    public function interpret(RawResponse $response): Job
    {
        if ($response->type === ResponseType::Found && isset($response->argument, $response->data)) {
            return new Job(new JobId($response->argument), $response->data);
        }
        throw match ($response->type) {
            ResponseType::NotFound => new JobNotFoundException(),
            ResponseType::Found => MalformedResponseException::expectedDataAndIntegerArgument(),
            default => new UnsupportedResponseException($response->type)
        };
    }

    protected function getCommandTemplate(): JobCommandTemplate
    {
        return new JobCommandTemplate("peek {id}");
    }
}
