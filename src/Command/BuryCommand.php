<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Response\ArrayResponse;

/**
 * The 'bury' command.
 * Puts a job into a 'buried' state, revived only by 'kick' command.
 */
class BuryCommand extends JobCommand implements ResponseParserInterface
{
    private $priority;

    public function __construct(JobIdInterface $job, int $priority)
    {
        parent::__construct($job);
        $this->priority = $priority;
    }

    public function getCommandLine(): string
    {
        return sprintf(
            'bury %u %u',
            $this->jobId,
            $this->priority
        );
    }

    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        if ($responseLine == ResponseInterface::RESPONSE_NOT_FOUND) {
            throw new Exception\JobNotFoundException(sprintf(
                '%s: Job %u is not reserved or does not exist.',
                $responseLine,
                $this->jobId
            ));
        } elseif ($responseLine == ResponseInterface::RESPONSE_BURIED) {
            return $this->createResponse(ResponseInterface::RESPONSE_BURIED);
        } else {
            throw new Exception('Unhandled response: '.$responseLine);
        }
    }
}
