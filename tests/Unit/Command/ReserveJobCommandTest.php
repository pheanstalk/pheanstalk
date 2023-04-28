<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\ReserveJobCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\ReserveJobCommand
 */
final class ReserveJobCommandTest extends JobCommandTestBase
{
    public function testInterpretReserved(): void
    {
        $command = $this->getSubject();

        $job = $command->interpret(new RawResponse(ResponseType::Reserved, "5", "abcdef"));
        Assert::assertSame("5", $job->getId());
        Assert::assertSame("abcdef", $job->getData());
    }

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Reserved];
    }

    protected function getSubject(JobIdInterface $jobId = null): ReserveJobCommand
    {
        return new ReserveJobCommand($jobId ?? new JobId(5));
    }
}
