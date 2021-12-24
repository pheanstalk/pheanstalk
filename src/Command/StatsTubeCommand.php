<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Parser\ChainedParser;
use Pheanstalk\Parser\TubeNotFoundExceptionParser;
use Pheanstalk\Parser\YamlDictionaryParser;
use Pheanstalk\Parser\YamlListParser;
use Pheanstalk\YamlResponseParser;

/**
 * The 'stats-tube' command.
 * Gives statistical information about the specified tube if it exists.
 */
class StatsTubeCommand extends TubeCommand
{
    public function getCommandLine(): string
    {
        return "stats-tube {$this->tube}";
    }

    public function getResponseParser(): \Pheanstalk\Contract\ResponseParserInterface
    {
        return new ChainedParser(
            new TubeNotFoundExceptionParser(),
            new YamlDictionaryParser()
        );
    }

    public function getType(): CommandType
    {
        return CommandType::STATS_TUBE;
    }
}
