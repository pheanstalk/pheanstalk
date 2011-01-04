<?php

/**
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_SocketWriteHistoryTest
	extends UnitTestCase
{
	public function testEmptyHistory()
	{
		$history = new Pheanstalk_Socket_WriteHistory(10);
		$this->assertFalse($history->isFull());
		$this->assertFalse($history->hasWrites());
		$this->assertFalse($history->isFullWithNoWrites());
	}

	public function testFullHistoryWithWrites()
	{
		$history = new Pheanstalk_Socket_WriteHistory(1);
		$history->log(1024);
		$this->assertTrue($history->isFull());
		$this->assertTrue($history->hasWrites());
		$this->assertFalse($history->isFullWithNoWrites());
	}

	public function testFullHistoryWithoutWrites()
	{
		$history = new Pheanstalk_Socket_WriteHistory(1);
		$history->log(0);
		$this->assertTrue($history->isFull());
		$this->assertFalse($history->hasWrites());
		$this->assertTrue($history->isFullWithNoWrites());
	}

	public function testFillingHistory()
	{
		$history = new Pheanstalk_Socket_WriteHistory(4);

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
		$history = new Pheanstalk_Socket_WriteHistory(1);

		foreach (array(null, false, 0, "", "0") as $input)
		{
			$this->assertEqual($history->log($input), $input);
			$this->assertTrue($history->isFullWithNoWrites());
		}

		foreach (array(true, 1, 2, "1", "2") as $input)
		{
			$this->assertEqual($history->log($input), $input);
			$this->assertFalse($history->isFullWithNoWrites());
		}
	}
}
