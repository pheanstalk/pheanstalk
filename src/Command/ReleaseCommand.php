<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Response\ArrayResponse;

/**
 * The 'release' command.
 *
 * Releases a reserved job back onto the ready queue.
 */
class ReleaseCommand extends JobCommand implements ResponseParserInterface
{
    private $priority;
    private $delay;

    public function __construct(JobIdInterface $job, int $priority, int $delay)
    {
        parent::__construct($job);
        $this->priority = $priority;
        $this->delay = $delay;
    }

    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
    public function getCommandLine(): string
    {
        return sprintf(
            'release %u %u %u',
            $this->jobId,
            $this->priority,
            $this->delay
        );
    }

    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        if ($responseLine == ResponseInterface::RESPONSE_BURIED) {
            throw new Exception\ServerOutOfMemoryException(sprintf(
                'Job %u %s: out of memory trying to grow data structure',
                $this->jobId,
                $responseLine
            ));
        }

        if ($responseLine == ResponseInterface::RESPONSE_NOT_FOUND) {
            throw new Exception\JobNotFoundException(sprintf(
                'Job %u %s: does not exist or is not reserved by client',
                $this->jobId,
                $responseLine
            ));
        }

        return $this->createResponse($responseLine);
    }
}
