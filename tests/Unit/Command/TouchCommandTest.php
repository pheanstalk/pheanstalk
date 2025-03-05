<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\TouchCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TouchCommand::class)]
final class TouchCommandTest extends JobCommandTestBase
{
    public function testInterpretTouched(): void
    {
        $command = $this->getSubject();
        $command->interpret(new RawResponse(ResponseType::Touched));
        $this->expectNotToPerformAssertions();
    }

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Touched];
    }

    protected function getSubject(?JobIdInterface $jobId = null): TouchCommand
    {
        return new TouchCommand($jobId ?? new JobId(5));
    }
}
