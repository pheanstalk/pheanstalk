<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\JobStats;
use Pheanstalk\Parser\YamlDictionaryParser;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\YamlResponseParser;

/**
 * The 'stats-job' command.
 *
 * Gives statistical information about the specified job if it exists.
 */
final class StatsJobCommand extends JobCommand
{
    public function interpret(RawResponse $response): JobStats
    {
        if ($response->type === ResponseType::Ok && isset($response->data)) {
            return JobStats::fromBeanstalkArray((new YamlDictionaryParser())->parse($response->data));
        }
        return match ($response->type) {
            ResponseType::NotFound => throw new JobNotFoundException(),
            ResponseType::Ok => throw MalformedResponseException::expectedData(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }

    protected function getCommandTemplate(): string
    {
        return "stats-job {id}";
    }
}
