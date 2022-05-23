<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Parser\YamlDictionaryParser;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\ServerStats;

/**
 * The 'stats' command.
 *
 * Statistical information about the system as a whole.
 *
 * @internal
 */
final class StatsCommand implements CommandInterface
{
    public function getCommandLine(): string
    {
        return 'stats';
    }

    public function interpret(RawResponse $response): ServerStats
    {
        if ($response->type === ResponseType::Ok && is_string($response->data)) {
            return ServerStats::fromBeanstalkArray((new YamlDictionaryParser())->parse($response->data));
        }
        return match ($response->type) {
            ResponseType::Ok => throw MalformedResponseException::expectedData(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }
}
