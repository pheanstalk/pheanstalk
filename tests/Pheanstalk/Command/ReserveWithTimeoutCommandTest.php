<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\ReserveWithTimeoutCommand;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Job;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\Success;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\ReserveWithTimeoutCommand
 */
final class ReserveWithTimeoutCommandTest extends CommandTest
{
    public function testInterpretDeadlineSoon(): void
    {
        $this->expectException(DeadlineSoonException::class);
        $this->getSubject()->interpret(new RawResponse(ResponseType::DeadlineSoon));
    }

    public function testInterpretTimedOut(): void
    {
        Assert::assertInstanceOf(Success::class, $this->getSubject()->interpret(new RawResponse(ResponseType::TimedOut)));
    }

    public function testInterpretReserved(): void
    {
        $id = "000112312311";
        $data = <<<DATA
            random stuff
        DATA;
        $job = $this->getSubject()->interpret(new RawResponse(ResponseType::Reserved, $id, $data));
        Assert::assertInstanceOf(Job::class, $job);

        Assert::assertSame($id, $job->getId());
        Assert::assertSame($data, $job->getData());
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::DeadlineSoon, ResponseType::TimedOut, ResponseType::Reserved];
    }

    protected function getSubject(): ReserveWithTimeoutCommand
    {
        return new ReserveWithTimeoutCommand(15);
    }
}
