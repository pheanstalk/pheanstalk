<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\ReserveJobCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\JobId;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\ReserveJobCommand
 */
final class ReserveJobCommandTest extends JobCommandTest
{
    public function testInterpretReserved(): void
    {
        $command = $this->getSubject();

        $job = $command->interpret(new RawResponse(ResponseType::Reserved, "5", "abcdef"));
        Assert::assertSame("5", $job->getId());
        Assert::assertSame("abcdef", $job->getData());
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Reserved];
    }

    protected function getSubject(JobIdInterface $jobId = null): ReserveJobCommand
    {
        return new ReserveJobCommand($jobId ?? new JobId(5));
    }
}
