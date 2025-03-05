<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\PeekJobCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PeekJobCommand::class)]
final class PeekJobCommandTest extends JobCommandTestBase
{
    public function testInterpretFound(): void
    {
        $command = $this->getSubject();

        $job = $command->interpret(new RawResponse(ResponseType::Found, "5", "abcdef"));
        Assert::assertSame("5", $job->getId());
        Assert::assertSame("abcdef", $job->getData());
    }

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Found];
    }

    protected function getSubject(?JobIdInterface $jobId = null): PeekJobCommand
    {
        return new PeekJobCommand($jobId ?? new JobId(5));
    }
}
