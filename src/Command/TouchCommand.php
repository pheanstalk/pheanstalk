<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\Response\EmptySuccessResponse;
use Pheanstalk\ResponseType;

/**
 * The 'touch' command.
 * The 'touch' command allows a worker to request more time to work on a job.
 * This is useful for jobs that potentially take a long time, but you still want
 * the benefits of a TTR pulling a job away from an unresponsive worker.  A worker
 * may periodically tell the server that it's still alive and processing a job
 * (e.g. it may do this on DEADLINE_SOON).
 */
class TouchCommand extends JobCommand
{
    public function getCommandLine(): string
    {
        return "touch {$this->jobId->getId()}";
    }

    public function getType(): CommandType
    {
        return CommandType::TOUCH;
    }

    public function getSuccessResponse(): ResponseType
    {
        return ResponseType::TOUCHED;
    }
}
