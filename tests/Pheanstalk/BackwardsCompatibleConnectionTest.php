<?php

/**
 * Tests methods that have been moved from Pheanstalk_Connection to Pheanstalk.
 * Relies on a running beanstalkd server.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_BackwardsCompatibleConnectionTest
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

		$this->_expectDeprecated('useTube');
		$this->expectException('Pheanstalk_Exception_ConnectionException');
		$connection->useTube('test');
	}

	public function testUseTube()
	{
		$connection = $this->_getConnection();

		$this->_expectDeprecated('getCurrentTube');
		$this->assertEqual($connection->getCurrentTube(), 'default');

		$this->_expectDeprecated('useTube');
		$connection->useTube('test');

		$this->_expectDeprecated('getCurrentTube');
		$this->assertEqual($connection->getCurrentTube(), 'test');
	}

	public function testWatchlist()
	{
		$connection = $this->_getConnection();

		$this->_expectDeprecated('getWatchedTubes');
		$this->assertEqual($connection->getWatchedTubes(), array('default'));

		$this->_expectDeprecated('watchTube');
		$connection->watchTube('test');

		$this->_expectDeprecated('getWatchedTubes');
		$this->assertEqual($connection->getWatchedTubes(), array('default', 'test'));

		$this->_expectDeprecated('ignoreTube');
		$connection->ignoreTube('default');

		$this->_expectDeprecated('getWatchedTubes');
		$this->assertEqual($connection->getWatchedTubes(), array('test'));
	}

	public function testIgnoreLastTube()
	{
		$connection = $this->_getConnection();

		$this->_expectDeprecated('ignoreTube');
		$this->expectException('Pheanstalk_Exception');
		$connection->ignoreTube('default');
	}

	public function testPutReserveAndDeleteData()
	{
		$connection = $this->_getConnection();

		$this->_expectDeprecated('put');
		$id = $connection->put(__METHOD__);
		$this->assertIsA($id, 'int');

		// reserve a job - can't assume it is the one just added
		$this->_expectDeprecated('reserve');
		$job = $connection->reserve();
		$this->assertIsA($job, 'Pheanstalk_Job');

		// delete the reserved job
		$this->_expectDeprecated('delete');
		$connection->delete($job);
	}

	public function testRelease()
	{
		$connection = $this->_getConnection();

		$this->_expectDeprecated('put');
		$connection->put(__METHOD__);

		$this->_expectDeprecated('reserve');
		$job = $connection->reserve();

		$this->_expectDeprecated('release');
		$connection->release($job);
	}

	public function testPutBuryAndKick()
	{
		$connection = $this->_getConnection();

		$this->_expectDeprecated('put');
		$id = $connection->put(__METHOD__);
		$this->assertIsA($id, 'int');

		// reserve a job - can't assume it is the one just added
		$this->_expectDeprecated('reserve');
		$job = $connection->reserve();
		$this->assertIsA($job, 'Pheanstalk_Job');

		// bury the reserved job
		$this->_expectDeprecated('bury');
		$connection->bury($job);

		// kick up to one job
		$this->_expectDeprecated('kick');
		$kickedCount = $connection->kick(1);
		$this->assertIsA($kickedCount, 'int');
		$this->assertEqual($kickedCount, 1,
			'there should be at least one buried (or delayed) job: %s');
	}

	public function testPutJobTooBig()
	{
		$connection = $this->_getConnection();

		$this->_expectDeprecated('put');
		$this->expectException('Pheanstalk_Exception');
		$connection->put(str_repeat('0', 0x10000));
	}

	// ----------------------------------------
	// private

	/**
	 * $method
	 */
	private function _expectDeprecated($method)
	{
		$this->expectError(sprintf(
			'Pheanstalk_Connection::%s() deprecated, use Pheanstalk::%s()',
			$method,
			$method
		));
	}

	private function _getConnection()
	{
		return new Pheanstalk_Connection(self::SERVER_HOST, self::SERVER_PORT);
	}
}

