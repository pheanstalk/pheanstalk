<?php

/**
 * Tests for the Pheanstalk_Connection.
 * Relies on a running beanstalkd server.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_ConnectionTest
	extends UnitTestCase
{
	const SERVER_HOST = 'localhost';
	const SERVER_PORT = '11300';

	public function testConnectionFailsToIncorrectPort()
	{
		$connection = new Pheanstalk_Connection(
			self::SERVER_HOST,
			self::SERVER_PORT + 1
		);
		$this->expectException('Pheanstalk_Exception_ConnectionException');
		$connection->useTube('test');
	}

	public function testUseTube()
	{
		$connection = $this->_getConnection();

		$this->assertEqual($connection->getCurrentTube(), 'default');
		$connection->useTube('test');
		$this->assertEqual($connection->getCurrentTube(), 'test');
	}

	public function testWatchlist()
	{
		$connection = $this->_getConnection();

		$this->assertEqual($connection->getWatchedTubes(), array('default'));
		$connection->watchTube('test');
		$this->assertEqual($connection->getWatchedTubes(), array('default', 'test'));
		$count = $connection->ignoreTube('default');
		$this->assertEqual($count, 1);
		$this->assertEqual($connection->getWatchedTubes(), array('test'));
	}

	public function testIgnoreLastTube()
	{
		$connection = $this->_getConnection();
		$this->expectException('Pheanstalk_Exception');
		$connection->ignoreTube('default');
	}

	public function testPutReserveAndDeleteData()
	{
		$connection = $this->_getConnection();

		$id = $connection->put(__METHOD__);
		$this->assertIsA($id, 'int');

		// reserve a job - can't assume it is the one just added
		$job = $connection->reserve();
		$this->assertIsA($job, 'Pheanstalk_Job');

		// delete the reserved job
		$connection->delete($job);
	}

	public function testRelease()
	{
		$connection = $this->_getConnection();

		$connection->put(__METHOD__);
		$job = $connection->reserve();
		$connection->release($job);
	}

	public function testPutBuryAndKick()
	{
		$connection = $this->_getConnection();

		$id = $connection->put(__METHOD__);
		$this->assertIsA($id, 'int');

		// reserve a job - can't assume it is the one just added
		$job = $connection->reserve();
		$this->assertIsA($job, 'Pheanstalk_Job');

		// bury the reserved job
		$connection->bury($job);

		// kick up to one job
		$kickedCount = $connection->kick(1);
		$this->assertIsA($kickedCount, 'int');
		$this->assertEqual($kickedCount, 1,
			'there should be at least one buried (or delayed) job: %s');
	}

	public function testPutJobTooBig()
	{
		$connection = $this->_getConnection();

		$this->expectException('Pheanstalk_Exception');
		$connection->put(str_repeat('0', 0x10000));
	}

	// ----------------------------------------
	// private

	private function _getConnection()
	{
		return new Pheanstalk_Connection(self::SERVER_HOST, self::SERVER_PORT);
	}
}

