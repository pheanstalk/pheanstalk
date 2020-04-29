<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\YamlResponseParser;

/**
 * The 'stats-job' command.
 *
 * Gives statistical information about the specified job if it exists.
 */
class StatsJobCommand extends JobCommand
{
    public function getCommandLine(): string
    {
        return sprintf('stats-job %u', $this->jobId);
    }

    public function getResponseParser(): ResponseParserInterface
    {
        return new YamlResponseParser(YamlResponseParser::MODE_DICT);
    }
}
