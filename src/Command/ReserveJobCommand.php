<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Job;
use Pheanstalk\JobId;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;

/**
 * The 'reserve-job' command.
 */
final class ReserveJobCommand extends JobCommand
{
    public function interpret(RawResponse $response): Job
    {
        if ($response->type === ResponseType::Reserved && isset($response->argument) && isset($response->data)) {
            return new Job($response->argument, $response->data);
        }
        return match ($response->type) {
            ResponseType::NotFound => throw new Exception\JobNotFoundException(),
            ResponseType::Reserved => throw Exception\MalformedResponseException::expectedDataAndIntegerArgument(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }

    protected function getCommandTemplate(): string
    {
        return "reserve-job {id}";
    }
}
