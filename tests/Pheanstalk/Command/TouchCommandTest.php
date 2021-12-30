<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\TouchCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\JobId;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\Success;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\TouchCommand
 */
final class TouchCommandTest extends JobCommandTest
{
    public function testInterpretTouched(): void
    {
        $command = $this->getSubject();
        $command->interpret(new RawResponse(ResponseType::Touched));
        $this->expectNotToPerformAssertions();
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Touched];
    }

    protected function getSubject(JobIdInterface $jobId = null): TouchCommand
    {
        return new TouchCommand($jobId ?? new JobId(5));
    }
}
