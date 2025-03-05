<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\ReserveWithTimeoutCommand;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Values\Job;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\Success;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReserveWithTimeoutCommand::class)]
final class ReserveWithTimeoutCommandTest extends CommandTestBase
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

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::DeadlineSoon, ResponseType::TimedOut, ResponseType::Reserved];
    }

    protected function getSubject(): ReserveWithTimeoutCommand
    {
        return new ReserveWithTimeoutCommand(15);
    }
}
