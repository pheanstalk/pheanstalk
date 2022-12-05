<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\JobCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\JobCommandTemplate;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;

/**
 * @covers \Pheanstalk\Command\JobCommand
 */
class ConcreteJobCommandTest extends JobCommandTest
{
    protected function getSupportedResponses(): array
    {
        return [ResponseType::NotFound];
    }

    protected function getSubject(JobIdInterface $jobId = null): JobCommand
    {
        return new class($jobId ?? new JobId(5)) extends JobCommand {
            public function interpret(
                RawResponse $response
            ): never {
                if ($response->type === ResponseType::NotFound) {
                    throw new JobNotFoundException();
                }
                throw new UnsupportedResponseException($response->type);
            }

            protected function getCommandTemplate(): JobCommandTemplate
            {
                return new JobCommandTemplate("random {id}");
            }
        };
    }
}
