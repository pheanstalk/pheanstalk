<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Parser\ChainedParser;
use Pheanstalk\Parser\JobNotFoundExceptionParser;
use Pheanstalk\Parser\YamlDictionaryParser;
use Pheanstalk\ResponseType;
use Pheanstalk\YamlResponseParser;

/**
 * The 'stats-job' command.
 *
 * Gives statistical information about the specified job if it exists.
 */
class StatsJobCommand extends JobCommand
{
    public function getResponseParser(): ResponseParserInterface
    {
        return new ChainedParser(
            new JobNotFoundExceptionParser(),
            new YamlDictionaryParser(),
        );
    }

    public function getType(): CommandType
    {
        return CommandType::STATS_JOB;
    }

    public function getSuccessResponse(): ResponseType
    {
        return ResponseType::OK;
    }
}
