<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Exception\UnsupportedResponseTypeException;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseType;

/**
 * The 'bury' command.
 * Puts a job into a 'buried' state, revived only by 'kick' command.
 */
final class BuryCommand extends JobCommand
{
    public function __construct(JobIdInterface $jobId,
        private readonly int $priority)
    {
        parent::__construct($jobId);
    }

    public function getCommandLine(): string
    {
        return "bury {$this->jobId->getId()} {$this->priority}";
    }


    public function getType(): CommandType
    {
        return CommandType::BURY;
    }

    public function getSuccessResponse(): ResponseType
    {
        return ResponseType::BURIED;
    }
}
