<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\ReleaseCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;

/**
 * @covers \Pheanstalk\Command\ReleaseCommand
 */
final class ReleaseCommandTest extends JobCommandTestBase
{
    public function testInterpretReleased(): void
    {
        $command = $this->getSubject();

        $command->interpret(new RawResponse(ResponseType::Released));
        $this->expectNotToPerformAssertions();
    }

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Released];
    }

    protected function getSubject(?JobIdInterface $jobId = null): ReleaseCommand
    {
        return new ReleaseCommand($jobId ?? new JobId(5), 123, 321);
    }
}
