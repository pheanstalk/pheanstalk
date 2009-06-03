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
		$pheanstalk->ignoreTube('default');
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

	public function testListTubes()
	{
		$pheanstalk = $this->_getFacade();
		$this->assertIsA($pheanstalk->listTubes(), 'array');
		$this->assertTrue(in_array('default', $pheanstalk->listTubes()));

		$pheanstalk->useTube('test1');
		$this->assertTrue(in_array('test1', $pheanstalk->listTubes()));

		$pheanstalk->watchTube('test2');
		$this->assertTrue(in_array('test2', $pheanstalk->listTubes()));
	}

	public function testPeek()
	{
		$pheanstalk = $this->_getFacade();

		$id = $pheanstalk
			->useTube('testpeek')
			->watchTube('testpeek')
			->ignoreTube('default')
			->put('test');

		$job = $pheanstalk->peek($id);

		$this->assertEqual($job->getData(), 'test');
	}

	public function testPeekReady()
	{
		$pheanstalk = $this->_getFacade();

		$id = $pheanstalk
			->useTube('testpeekready')
			->watchTube('testpeekready')
			->ignoreTube('default')
			->put('test');

		$job = $pheanstalk->peekReady();

		$this->assertEqual($job->getData(), 'test');
	}

	public function testPeekDelayed()
	{
		$pheanstalk = $this->_getFacade();

		$id = $pheanstalk
			->useTube('testpeekdelayed')
			->watchTube('testpeekdelayed')
			->ignoreTube('default')
			->put('test', 0, 2);

		$job = $pheanstalk->peekDelayed();

		$this->assertEqual($job->getData(), 'test');
	}

	public function testPeekBuried()
	{
		$pheanstalk = $this->_getFacade();

		$id = $pheanstalk
			->useTube('testpeekburied')
			->watchTube('testpeekburied')
			->ignoreTube('default')
			->put('test');

		$job = $pheanstalk->reserve($id);
		$pheanstalk->bury($job);

		$job = $pheanstalk->peekBuried();

		$this->assertEqual($job->getData(), 'test');
	}

	// ----------------------------------------
	// private

	private function _getFacade()
	{
		return new Pheanstalk(self::SERVER_HOST);
	}
}

