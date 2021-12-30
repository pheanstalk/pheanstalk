<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\PeekJobCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\JobId;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\PeekJobCommand
 */
final class PeekJobCommandTest extends JobCommandTest
{
    public function testInterpretFound(): void
    {
        $command = $this->getSubject();

        $job = $command->interpret(new RawResponse(ResponseType::Found, "5", "abcdef"));
        Assert::assertSame("5", $job->getId());
        Assert::assertSame("abcdef", $job->getData());
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Found];
    }

    protected function getSubject(JobIdInterface $jobId = null): PeekJobCommand
    {
        return new PeekJobCommand($jobId ?? new JobId(5));
    }
}
