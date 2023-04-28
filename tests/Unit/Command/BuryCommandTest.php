<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\BuryCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;

/**
 * @covers \Pheanstalk\Command\BuryCommand
 */
final class BuryCommandTest extends JobCommandTestBase
{
    public function testInterpretBuried(): void
    {
        $this->expectNotToPerformAssertions();
        $command = new BuryCommand(new JobId(5), 2);

        $command->interpret(new RawResponse(ResponseType::Buried));
    }

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Buried];
    }

    protected function getSubject(JobIdInterface $jobId = null): BuryCommand
    {
        return new BuryCommand($jobId ?? new JobId(5), 1);
    }
}
