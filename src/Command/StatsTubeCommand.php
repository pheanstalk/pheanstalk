<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\TubeNotFoundException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\JobStats;
use Pheanstalk\Parser\YamlDictionaryParser;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\TubeStats;

/**
 * The 'stats-tube' command.
 * Gives statistical information about the specified tube if it exists.
 */
final class StatsTubeCommand extends TubeCommand
{
    public function interpret(RawResponse $response): TubeStats
    {
        if ($response->type === ResponseType::Ok && isset($response->data)) {
            return TubeStats::fromBeanstalkArray((new YamlDictionaryParser())->parse($response->data));
        }
        return match ($response->type) {
            ResponseType::NotFound => throw new TubeNotFoundException(),
            ResponseType::Ok => throw MalformedResponseException::expectedData(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }

    protected function getCommandTemplate(): string
    {
        return "stats-tube {tube}";
    }
}
