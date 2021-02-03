<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseLine;

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

    public function parseResponse(ResponseLine $responseLine, ?string $responseData): ResponseInterface
    {
        if ($responseLine->getName() === ResponseInterface::RESPONSE_NOT_FOUND) {
            throw new JobNotFoundException();
        }

        return $this->createResponse($responseLine->getName(), [
            'id'      => (int) $responseLine->getArguments()[0],
            'jobdata' => $responseData,
        ]);
    }
}
