<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Response\ArrayResponse;

/**
 * The 'touch' command.
 * The 'touch' command allows a worker to request more time to work on a job.
 * This is useful for jobs that potentially take a long time, but you still want
 * the benefits of a TTR pulling a job away from an unresponsive worker.  A worker
 * may periodically tell the server that it's still alive and processing a job
 * (e.g. it may do this on DEADLINE_SOON).
 */
class TouchCommand extends JobCommand implements ResponseParserInterface
{
    public function getCommandLine(): string
    {
        return sprintf('touch %u', $this->jobId);
    }

    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
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
