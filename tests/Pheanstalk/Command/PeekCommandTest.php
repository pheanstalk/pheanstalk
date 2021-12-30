<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\PeekCommand;
use Pheanstalk\Job;
use Pheanstalk\JobState;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\Success;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\PeekCommand
 */
final class PeekCommandTest extends CommandTest
{
    public function testPeekReserved(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new PeekCommand(JobState::RESERVED);
    }

    public function testInterpretNotFound(): void
    {
        $result = $this->getSubject()->interpret(new RawResponse(ResponseType::NotFound));
        Assert::assertInstanceOf(Success::class, $result);
    }

    public function testInterpretFound(): void
    {
        $data = <<<DATA
            bacdefwf
            afwwefawaw
        DATA;
        $id = "123141515151325132515";
        $result = $this->getSubject()->interpret(new RawResponse(ResponseType::Found, $id, $data));

        Assert::assertInstanceOf(Job::class, $result);
        Assert::assertSame($id, $result->getId());
        Assert::assertSame($data, $result->getData());
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Found];
    }

    protected function getSubject(JobState $state = null): PeekCommand
    {
        return new PeekCommand($state ?? JobState::READY);
    }
}
