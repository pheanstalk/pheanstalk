<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Socket\WriteHistory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @author  Paul Annesley
 */
class SocketWriteHistoryTest extends TestCase
{
    public function testEmptyHistory()
    {
        $history = new WriteHistory(10);
        Assert::assertFalse($history->isFull());
        Assert::assertFalse($history->hasWrites());
        Assert::assertFalse($history->isFullWithNoWrites());
    }

    public function testFullHistoryWithWrites()
    {
        $history = new WriteHistory(1);
        $history->log(1024);
        Assert::assertTrue($history->isFull());
        Assert::assertTrue($history->hasWrites());
        Assert::assertFalse($history->isFullWithNoWrites());
    }

    public function testFullHistoryWithoutWrites()
    {
        $history = new WriteHistory(1);
        $history->log(0);
        Assert::assertTrue($history->isFull());
        Assert::assertFalse($history->hasWrites());
        Assert::assertTrue($history->isFullWithNoWrites());
    }

    public function testFillingHistory()
    {
        $history = new WriteHistory(4);

        $history->log(0);
        Assert::assertFalse($history->isFull());
        Assert::assertFalse($history->hasWrites());

        $history->log(false);
        Assert::assertFalse($history->isFull());
        Assert::assertFalse($history->hasWrites());

        $history->log(1024);
        Assert::assertFalse($history->isFull());
        Assert::assertTrue($history->hasWrites());

        $history->log(0);
        Assert::assertTrue($history->isFull());
        Assert::assertTrue($history->hasWrites());
        Assert::assertFalse($history->isFullWithNoWrites());

        $history->log(0);
        Assert::assertTrue($history->isFull());
        Assert::assertTrue($history->hasWrites());

        $history->log(0);
        Assert::assertTrue($history->isFull());
        Assert::assertTrue($history->hasWrites());

        $history->log(0);
        Assert::assertTrue($history->isFull());
        Assert::assertFalse($history->hasWrites());
        Assert::assertTrue($history->isFullWithNoWrites());
    }

    public function testDifferentInputTypes()
    {
        $history = new WriteHistory(1);

        foreach ([null, false, 0, '', '0'] as $input) {
            $history->log($input);
            Assert::assertTrue($history->isFullWithNoWrites());
        }

        foreach ([true, 1, 2, '1', '2'] as $input) {
            $history->log($input);
            Assert::assertFalse($history->isFullWithNoWrites());
        }
    }
}
