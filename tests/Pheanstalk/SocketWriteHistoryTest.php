<?php
declare(strict_types=1);

namespace Pheanstalk\Tests;

use Pheanstalk\Socket\WriteHistory;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class SocketWriteHistoryTest extends BaseTestCase
{
    public function testEmptyHistory()
    {
        $history = new WriteHistory(10);
        $this->assertFalse($history->isFull());
        $this->assertFalse($history->hasWrites());
        $this->assertFalse($history->isFullWithNoWrites());
    }

    public function testFullHistoryWithWrites()
    {
        $history = new WriteHistory(1);
        $history->log(1024);
        $this->assertTrue($history->isFull());
        $this->assertTrue($history->hasWrites());
        $this->assertFalse($history->isFullWithNoWrites());
    }

    public function testFullHistoryWithoutWrites()
    {
        $history = new WriteHistory(1);
        $history->log(0);
        $this->assertTrue($history->isFull());
        $this->assertFalse($history->hasWrites());
        $this->assertTrue($history->isFullWithNoWrites());
    }

    public function testFillingHistory()
    {
        $history = new WriteHistory(4);

        $history->log(0);
        $this->assertFalse($history->isFull());
        $this->assertFalse($history->hasWrites());

        $history->log(false);
        $this->assertFalse($history->isFull());
        $this->assertFalse($history->hasWrites());

        $history->log(1024);
        $this->assertFalse($history->isFull());
        $this->assertTrue($history->hasWrites());

        $history->log(0);
        $this->assertTrue($history->isFull());
        $this->assertTrue($history->hasWrites());
        $this->assertFalse($history->isFullWithNoWrites());

        $history->log(0);
        $this->assertTrue($history->isFull());
        $this->assertTrue($history->hasWrites());

        $history->log(0);
        $this->assertTrue($history->isFull());
        $this->assertTrue($history->hasWrites());

        $history->log(0);
        $this->assertTrue($history->isFull());
        $this->assertFalse($history->hasWrites());
        $this->assertTrue($history->isFullWithNoWrites());
    }

    public function testDifferentInputTypes()
    {
        $history = new WriteHistory(1);

        foreach ([null, false, 0, '', '0'] as $input) {
            $history->log($input);
            $this->assertTrue($history->isFullWithNoWrites());
        }

        foreach ([true, 1, 2, '1', '2'] as $input) {
            $history->log($input);
            $this->assertFalse($history->isFullWithNoWrites());
        }
    }
}
