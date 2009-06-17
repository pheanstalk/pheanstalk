<?php

/**
 * Tests deprecated methods.
 *  - methods that have been moved from Pheanstalk_Connection to Pheanstalk,
 *  - methods that have been renamed.
 *
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

		$this->_expectDeprecatedConnectionMethod('useTube');
		$this->expectException('Pheanstalk_Exception_ConnectionException');
		$connection->useTube('test');
	}

	public function testUseTube()
	{
		$connection = $this->_getConnection();

		$this->_expectDeprecatedConnectionMethod('getCurrentTube');
		$this->_expectDeprecatedFacadeMethod('getCurrentTube', 'listTubeUsed');
		$this->assertEqual($connection->getCurrentTube(), 'default');

		$this->_expectDeprecatedConnectionMethod('useTube');
		$connection->useTube('test');

		$this->_expectDeprecatedConnectionMethod('getCurrentTube');
		$this->_expectDeprecatedFacadeMethod('getCurrentTube', 'listTubeUsed');
		$this->assertEqual($connection->getCurrentTube(), 'test');
	}

	public function testWatchlist()
	{
		$connection = $this->_getConnection();

		$this->_expectDeprecatedConnectionMethod('getWatchedTubes');
		$this->_expectDeprecatedFacadeMethod('getWatchedTubes', 'listTubesWatched');
		$this->assertEqual($connection->getWatchedTubes(), array('default'));

		$this->_expectDeprecatedConnectionMethod('watchTube');
		$this->_expectDeprecatedFacadeMethod('watchTube', 'watch');
		$connection->watchTube('test');

		$this->_expectDeprecatedConnectionMethod('getWatchedTubes');
		$this->_expectDeprecatedFacadeMethod('getWatchedTubes', 'listTubesWatched');
		$this->assertEqual($connection->getWatchedTubes(), array('default', 'test'));

		$this->_expectDeprecatedConnectionMethod('ignoreTube');
		$this->_expectDeprecatedFacadeMethod('ignoreTube', 'ignore');
		$connection->ignoreTube('default');

		$this->_expectDeprecatedConnectionMethod('getWatchedTubes');
		$this->_expectDeprecatedFacadeMethod('getWatchedTubes', 'listTubesWatched');
		$this->assertEqual($connection->getWatchedTubes(), array('test'));
	}

	public function testIgnoreLastTube()
	{
		$connection = $this->_getConnection();

		$this->_expectDeprecatedConnectionMethod('ignoreTube');
		$this->_expectDeprecatedFacadeMethod('ignoreTube', 'ignore');
		$this->expectException('Pheanstalk_Exception');
		$connection->ignoreTube('default');
	}

	public function testPutReserveAndDeleteData()
	{
		$connection = $this->_getConnection();

		$this->_expectDeprecatedConnectionMethod('put');
		$id = $connection->put(__METHOD__);
		$this->assertIsA($id, 'int');

		// reserve a job - can't assume it is the one just added
		$this->_expectDeprecatedConnectionMethod('reserve');
		$job = $connection->reserve();
		$this->assertIsA($job, 'Pheanstalk_Job');

		// delete the reserved job
		$this->_expectDeprecatedConnectionMethod('delete');
		$connection->delete($job);
	}

	public function testRelease()
	{
		$connection = $this->_getConnection();

		$this->_expectDeprecatedConnectionMethod('put');
		$connection->put(__METHOD__);

		$this->_expectDeprecatedConnectionMethod('reserve');
		$job = $connection->reserve();

		$this->_expectDeprecatedConnectionMethod('release');
		$connection->release($job);
	}

	public function testPutBuryAndKick()
	{
		$connection = $this->_getConnection();

		$this->_expectDeprecatedConnectionMethod('put');
		$id = $connection->put(__METHOD__);
		$this->assertIsA($id, 'int');

		// reserve a job - can't assume it is the one just added
		$this->_expectDeprecatedConnectionMethod('reserve');
		$job = $connection->reserve();
		$this->assertIsA($job, 'Pheanstalk_Job');

		// bury the reserved job
		$this->_expectDeprecatedConnectionMethod('bury');
		$connection->bury($job);

		// kick up to one job
		$this->_expectDeprecatedConnectionMethod('kick');
		$kickedCount = $connection->kick(1);
		$this->assertIsA($kickedCount, 'int');
		$this->assertEqual($kickedCount, 1,
			'there should be at least one buried (or delayed) job: %s');
	}

	public function testPutJobTooBig()
	{
		$connection = $this->_getConnection();

		$this->_expectDeprecatedConnectionMethod('put');
		$this->expectException('Pheanstalk_Exception');
		$connection->put(str_repeat('0', 0x10000));
	}

	public function testFacadeUseTube()
	{
		$pheanstalk = $this->_getFacade();

		$this->_expectDeprecatedFacadeMethod('getCurrentTube', 'listTubeUsed');
		$this->assertEqual($pheanstalk->getCurrentTube(), 'default');

		$pheanstalk->useTube('test');

		$this->_expectDeprecatedFacadeMethod('getCurrentTube', 'listTubeUsed');
		$this->assertEqual($pheanstalk->getCurrentTube(), 'test');
	}

	public function testFacadeWatchlist()
	{
		$pheanstalk = $this->_getFacade();

		$this->_expectDeprecatedFacadeMethod('getWatchedTubes', 'listTubesWatched');
		$this->assertEqual($pheanstalk->getWatchedTubes(), array('default'));

		$this->_expectDeprecatedFacadeMethod('watchTube', 'watch');
		$pheanstalk->watchTube('test');

		$this->_expectDeprecatedFacadeMethod('getWatchedTubes', 'listTubesWatched');
		$this->assertEqual($pheanstalk->getWatchedTubes(), array('default', 'test'));

		$this->_expectDeprecatedFacadeMethod('ignoreTube', 'ignore');
		$pheanstalk->ignoreTube('default');

		$this->_expectDeprecatedFacadeMethod('getWatchedTubes', 'listTubesWatched');
		$this->assertEqual($pheanstalk->getWatchedTubes(), array('test'));
	}

	// ----------------------------------------
	// private

	/**
	 * Expect a 'method deprecated' PHP warning
	 * @param string $method
	 */
	private function _expectDeprecatedConnectionMethod($method)
	{
		$this->expectError(sprintf(
			'Pheanstalk_Connection::%s() deprecated, use Pheanstalk::%s()',
			$method,
			$method
		));
	}

	/**
	 * Expect a 'method deprecated' PHP warning
	 * @param string $old
	 * @param string $new
	 */
	private function _expectDeprecatedFacadeMethod($old, $new)
	{
		$this->expectError(sprintf(
			'Pheanstalk::%s() deprecated, use Pheanstalk::%s()',
			$old,
			$new
		));
	}

	private function _getConnection()
	{
		return new Pheanstalk_Connection(self::SERVER_HOST, self::SERVER_PORT);
	}

	private function _getFacade()
	{
		return new Pheanstalk(self::SERVER_HOST, self::SERVER_PORT);
	}
}
