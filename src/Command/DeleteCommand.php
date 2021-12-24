<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Parser\ChainedParser;
use Pheanstalk\Parser\EmptySuccessParser;
use Pheanstalk\Parser\JobNotFoundExceptionParser;
use Pheanstalk\ResponseType;

/**
 * The 'delete' command.
 * Permanently deletes an already-reserved job.
 */
final class DeleteCommand extends JobCommand
{
    public function getType(): CommandType
    {
        return CommandType::DELETE;
    }

    public function getSuccessResponse(): ResponseType
    {
        return ResponseType::DELETED;
    }
}
