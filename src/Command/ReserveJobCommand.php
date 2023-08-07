<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\Job;
use Pheanstalk\Values\JobCommandTemplate;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;

/**
 * The 'reserve-job' command.
 */
final class ReserveJobCommand extends JobCommand
{
    public function interpret(RawResponse $response): Job
    {
        return match (true) {
            $response->type === ResponseType::Reserved && isset($response->argument, $response->data) => new Job(new JobId($response->argument), $response->data),
            $response->type === ResponseType::Reserved => throw Exception\MalformedResponseException::expectedDataAndIntegerArgument(),
            $response->type === ResponseType::NotFound => throw new Exception\JobNotFoundException(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }

    protected function getCommandTemplate(): JobCommandTemplate
    {
        return new JobCommandTemplate("reserve-job {id}");
    }
}
