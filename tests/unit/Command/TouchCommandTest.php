<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\TouchCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;

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
