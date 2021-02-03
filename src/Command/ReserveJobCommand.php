<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\Response\ArrayResponse;

/**
 * The 'reserve-job' command.
 *
 * Reserves/locks a specific job, new in beanstalkd 1.12+
 */
class ReserveJobCommand extends AbstractCommand implements ResponseParserInterface
{
    private $job;
    public function __construct(JobIdInterface $job)
    {
       $this->job = $job->getId();
    }

    public function getCommandLine(): string
    {
        return sprintf('reserve-job %d', $this->job);
    }

    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        if ($responseLine === ResponseInterface::RESPONSE_NOT_FOUND) {
            throw new JobNotFoundException();
        }

        list($code, $id) = explode(' ', $responseLine);
        return $this->createResponse($code, [
            'id'      => (int) $id,
            'jobdata' => $responseData,
        ]);
    }
}
