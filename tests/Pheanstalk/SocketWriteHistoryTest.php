<?php

namespace Pheanstalk;

use Pheanstalk\Socket\WriteHistory;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * @author  Paul Annesley
 */
class SocketWriteHistoryTest extends TestCase
{
    public function testEmptyHistory()
    {
        $history = new WriteHistory(10);
        self::assertFalse($history->isFull());
        self::assertFalse($history->hasWrites());
        self::assertFalse($history->isFullWithNoWrites());
    }

    public function testFullHistoryWithWrites()
    {
        $history = new WriteHistory(1);
        $history->log(1024);
        self::assertTrue($history->isFull());
        self::assertTrue($history->hasWrites());
        self::assertFalse($history->isFullWithNoWrites());
    }

    public function testFullHistoryWithoutWrites()
    {
        $history = new WriteHistory(1);
        $history->log(0);
        self::assertTrue($history->isFull());
        self::assertFalse($history->hasWrites());
        self::assertTrue($history->isFullWithNoWrites());
    }

    public function testFillingHistory()
    {
        $history = new WriteHistory(4);

        $history->log(0);
        self::assertFalse($history->isFull());
        self::assertFalse($history->hasWrites());

        $history->log(false);
        self::assertFalse($history->isFull());
        self::assertFalse($history->hasWrites());

        $history->log(1024);
        self::assertFalse($history->isFull());
        self::assertTrue($history->hasWrites());

        $history->log(0);
        self::assertTrue($history->isFull());
        self::assertTrue($history->hasWrites());
        self::assertFalse($history->isFullWithNoWrites());

        $history->log(0);
        self::assertTrue($history->isFull());
        self::assertTrue($history->hasWrites());

        $history->log(0);
        self::assertTrue($history->isFull());
        self::assertTrue($history->hasWrites());

        $history->log(0);
        self::assertTrue($history->isFull());
        self::assertFalse($history->hasWrites());
        self::assertTrue($history->isFullWithNoWrites());
    }

    public function testDifferentInputTypes()
    {
        $history = new WriteHistory(1);

        foreach ([null, false, 0, '', '0'] as $input) {
            $history->log($input);
            self::assertTrue($history->isFullWithNoWrites());
        }

        foreach ([true, 1, 2, '1', '2'] as $input) {
            $history->log($input);
            self::assertFalse($history->isFullWithNoWrites());
        }
    }
}
