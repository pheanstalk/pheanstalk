<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseType;

/**
 * The 'kick-job' command.
 *
 * Kicks a specific buried or delayed job into a 'ready' state.
 *
 * A variant of kick that operates with a single job. If the given job
 * exists and is in a buried or delayed state, it will be moved to the
 * ready queue of the the same tube where it currently belongs.
 *
 */
class KickJobCommand extends JobCommand
{
    public function getCommandLine(): string
    {
        return "kick-job {$this->jobId->getId()}";
    }

    public function getType(): CommandType
    {
        return CommandType::KICK_JOB;
    }

    public function getSuccessResponse(): ResponseType
    {
        return ResponseType::KICKED;
    }
}
