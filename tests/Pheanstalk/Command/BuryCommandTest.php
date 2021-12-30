<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\BuryCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\JobId;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\Success;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\BuryCommand
 */
final class BuryCommandTest extends JobCommandTest
{
    public function testInterpretBuried(): void
    {
        $this->expectNotToPerformAssertions();
        $command = new BuryCommand(new JobId(5), 2);

        $command->interpret(new RawResponse(ResponseType::Buried));
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Buried];
    }

    protected function getSubject(JobIdInterface $jobId = null): BuryCommand
    {
        return new BuryCommand($jobId ?? new JobId(5), 1);
    }
}
