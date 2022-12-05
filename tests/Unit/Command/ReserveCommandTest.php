<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\ReserveCommand;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Exception\TimedOutException;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\ReserveCommand
 */
final class ReserveCommandTest extends CommandTest
{
    public function testInterpretDeadlineSoon(): void
    {
        $this->expectException(DeadlineSoonException::class);
        $this->getSubject()->interpret(new RawResponse(ResponseType::DeadlineSoon));
    }

    public function testInterpretTimedOut(): void
    {
        $this->expectException(TimedOutException::class);
        $this->getSubject()->interpret(new RawResponse(ResponseType::TimedOut));
    }

    public function testInterpretReserved(): void
    {
        $id = "000112312311";
        $data = <<<DATA
            random stuff
        DATA;
        $job = $this->getSubject()->interpret(new RawResponse(ResponseType::Reserved, $id, $data));

        Assert::assertSame($id, $job->getId());
        Assert::assertSame($data, $job->getData());
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::DeadlineSoon, ResponseType::TimedOut, ResponseType::Reserved];
    }

    protected function getSubject(): ReserveCommand
    {
        return new ReserveCommand();
    }
}
