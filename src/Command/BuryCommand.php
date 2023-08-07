<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Exception;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\JobCommandTemplate;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\Success;

/**
 * The 'bury' command.
 * Puts a job into a 'buried' state, revived only by 'kick' command.
 */
final class BuryCommand extends JobCommand
{
    public function __construct(
        JobIdInterface $jobId,
        private readonly int $priority
    ) {
        parent::__construct($jobId);
    }

    protected function getCommandTemplate(): JobCommandTemplate
    {
        return new JobCommandTemplate("bury {id} {$this->priority}");
    }

    public function interpret(RawResponse $response): Success
    {
        return match ($response->type) {
            ResponseType::NotFound => throw new Exception\JobNotFoundException(),
            ResponseType::Buried => new Success(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }
}
