<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Parser\YamlDictionaryParser;
use Pheanstalk\Values\JobCommandTemplate;
use Pheanstalk\Values\JobStats;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;

/**
 * The 'stats-job' command.
 *
 * Gives statistical information about the specified job if it exists.
 *
 */
final class StatsJobCommand extends JobCommand
{
    public function interpret(RawResponse $response): JobStats
    {
        return match (true) {
            $response->type === ResponseType::Ok && isset($response->data) => JobStats::fromBeanstalkArray((new YamlDictionaryParser())->parse($response->data)),
            $response->type === ResponseType::NotFound => throw new JobNotFoundException(),
            $response->type === ResponseType::Ok => throw MalformedResponseException::expectedData(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }

    protected function getCommandTemplate(): JobCommandTemplate
    {
        return new JobCommandTemplate("stats-job {id}");
    }
}
