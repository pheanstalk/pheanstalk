<?php

/**
 * Tests the Pheanstalk facade (the base class).
 * Relies on a running beanstalkd server.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_FacadeConnectionTest
	extends UnitTestCase
{
	const SERVER_HOST = 'localhost';

	public function testUseTube()
	{
		$pheanstalk = $this->_getFacade();

		$this->assertEqual($pheanstalk->getCurrentTube(), 'default');
		$pheanstalk->useTube('test');
		$this->assertEqual($pheanstalk->getCurrentTube(), 'test');
	}

	public function testWatchlist()
	{
		$pheanstalk = $this->_getFacade();

		$this->assertEqual($pheanstalk->getWatchedTubes(), array('default'));
		$pheanstalk->watchTube('test');
		$this->assertEqual($pheanstalk->getWatchedTubes(), array('default', 'test'));
		$count = $pheanstalk->ignoreTube('default');
		$this->assertEqual($count, 1);
		$this->assertEqual($pheanstalk->getWatchedTubes(), array('test'));
	}

	public function testIgnoreLastTube()
	{
		$pheanstalk = $this->_getFacade();
		$this->expectException('Pheanstalk_Exception');
		$pheanstalk->ignoreTube('default');
	}

	public function testPutReserveAndDeleteData()
	{
		$pheanstalk = $this->_getFacade();

		$id = $pheanstalk->put(__METHOD__);
		$this->assertIsA($id, 'int');

		// reserve a job - can't assume it is the one just added
		$job = $pheanstalk->reserve();
		$this->assertIsA($job, 'Pheanstalk_Job');

		// delete the reserved job
		$pheanstalk->delete($job);
	}

	public function testRelease()
	{
		$pheanstalk = $this->_getFacade();

		$pheanstalk->put(__METHOD__);
		$job = $pheanstalk->reserve();
		$pheanstalk->release($job);
	}

	public function testPutBuryAndKick()
	{
		$pheanstalk = $this->_getFacade();

		$id = $pheanstalk->put(__METHOD__);
		$this->assertIsA($id, 'int');

		// reserve a job - can't assume it is the one just added
		$job = $pheanstalk->reserve();
		$this->assertIsA($job, 'Pheanstalk_Job');

		// bury the reserved job
		$pheanstalk->bury($job);

		// kick up to one job
		$kickedCount = $pheanstalk->kick(1);
		$this->assertIsA($kickedCount, 'int');
		$this->assertEqual($kickedCount, 1,
			'there should be at least one buried (or delayed) job: %s');
	}

	public function testPutJobTooBig()
	{
		$pheanstalk = $this->_getFacade();

		$this->expectException('Pheanstalk_Exception');
		$pheanstalk->put(str_repeat('0', 0x10000));
	}

	public function testTouch()
	{
		$pheanstalk = $this->_getFacade();

		$pheanstalk->put(__METHOD__);
		$job = $pheanstalk->reserve();
		$pheanstalk->touch($job);
	}

	// ----------------------------------------
	// private

	private function _getFacade()
	{
		return new Pheanstalk(self::SERVER_HOST);
	}
}

