<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Parser\YamlDictionaryParser;

/**
 * The 'stats' command.
 *
 * Statistical information about the system as a whole.
 */
class StatsCommand extends AbstractCommand
{
    public function getResponseParser(): \Pheanstalk\Contract\ResponseParserInterface
    {
        return new YamlDictionaryParser();
    }

    public function getType(): CommandType
    {
        return CommandType::STATS;
    }
}
