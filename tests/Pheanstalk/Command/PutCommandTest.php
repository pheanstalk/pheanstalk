<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\PutCommand;
use Pheanstalk\Exception\ExpectedCrlfException;
use Pheanstalk\Exception\JobBuriedException;
use Pheanstalk\Exception\JobTooBigException;
use Pheanstalk\Exception\ServerDrainingException;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\PutCommand
 */
final class PutCommandTest extends CommandTest
{
    public function testInterpretBuried(): void
    {
        $id = "123123";
        $this->expectException(JobBuriedException::class);
        try {
            $this->getSubject()->interpret(new RawResponse(ResponseType::Buried, "123123"));
        } catch (JobBuriedException $e) {
            Assert::assertSame($id, $e->jobId->getId());
            throw $e;
        }
    }

    public function testInterpretExpectedCrlf(): void
    {
        $this->expectException(ExpectedCrlfException::class);
        $this->getSubject()->interpret(new RawResponse(ResponseType::ExpectedCrlf));
    }
    public function testInterpretDraining(): void
    {
        $this->expectException(ServerDrainingException::class);
        $this->getSubject()->interpret(new RawResponse(ResponseType::Draining));
    }

    public function testInterpretJobTooBig(): void
    {
        $this->expectException(JobTooBigException::class);
        $this->getSubject()->interpret(new RawResponse(ResponseType::JobTooBig));
    }

    public function testInterpretInserted(): void
    {
        $id = "15997";
        Assert::assertSame($id, $this->getSubject()->interpret(new RawResponse(ResponseType::Inserted, $id))->getId());
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::Buried, ResponseType::ExpectedCrlf, ResponseType::Draining, ResponseType::JobTooBig, ResponseType::Inserted];
    }

    protected function getSubject(): PutCommand
    {
        return new PutCommand("data", 5, 4, 3);
    }


    /**
     * @phpstan-return iterable<array{0: string, 1: int}>
     */
    public function dataProvider(): iterable
    {
        yield ["ϸϹϻ", 6];
        /**
         * Test some invalid UTF-8 sequences
         * @see https://www.cl.cam.ac.uk/~mgk25/ucs/examples/UTF-8-test.txt
         */
        yield [(string) hex2bin("fe"), 1];
        yield [(string) hex2bin("ff"), 1];
        yield [(string) hex2bin("fefeffff"), 4];
        yield [(string) hex2bin("c0af"), 2];
        yield [(string) hex2bin("eda080"), 3];
        yield [(string) hex2bin("eda080edb080"), 6];
        for ($i = 1; $i < 10; $i++) {
            yield [random_bytes(32), 32];
        }
    }

    /**
     * @dataProvider dataProvider
     */
    public function testData(string $data, int $expectedLength): void
    {
        $command = new PutCommand($data, 5, 5, 4);
        Assert::assertSame("put 5 5 4 {$expectedLength}", $command->getCommandLine());
        Assert::assertSame($data, $command->getData());
    }
}
