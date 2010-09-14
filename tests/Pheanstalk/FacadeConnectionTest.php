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

		$this->assertEqual($pheanstalk->listTubeUsed(), 'default');
		$pheanstalk->useTube('test');
		$this->assertEqual($pheanstalk->listTubeUsed(), 'test');
	}

	public function testWatchlist()
	{
		$pheanstalk = $this->_getFacade();

		$this->assertEqual($pheanstalk->listTubesWatched(), array('default'));
		$pheanstalk->watch('test');
		$this->assertEqual($pheanstalk->listTubesWatched(), array('default', 'test'));
		$pheanstalk->ignore('default');
		$this->assertEqual($pheanstalk->listTubesWatched(), array('test'));
	}

	public function testIgnoreLastTube()
	{
		$pheanstalk = $this->_getFacade();

		$this->expectException('Pheanstalk_Exception');
		$pheanstalk->ignore('default');
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

		$pheanstalk->watch('test2');
		$this->assertTrue(in_array('test2', $pheanstalk->listTubes()));
	}

	public function testPeek()
	{
		$pheanstalk = $this->_getFacade();

		$id = $pheanstalk
			->useTube('testpeek')
			->watch('testpeek')
			->ignore('default')
			->put('test');

		$job = $pheanstalk->peek($id);

		$this->assertEqual($job->getData(), 'test');
	}

	public function testPeekReady()
	{
		$pheanstalk = $this->_getFacade();

		$id = $pheanstalk
			->useTube('testpeekready')
			->watch('testpeekready')
			->ignore('default')
			->put('test');

		$job = $pheanstalk->peekReady();

		$this->assertEqual($job->getData(), 'test');
	}

	public function testPeekDelayed()
	{
		$pheanstalk = $this->_getFacade();

		$id = $pheanstalk
			->useTube('testpeekdelayed')
			->watch('testpeekdelayed')
			->ignore('default')
			->put('test', 0, 2);

		$job = $pheanstalk->peekDelayed();

		$this->assertEqual($job->getData(), 'test');
	}

	public function testPeekBuried()
	{
		$pheanstalk = $this->_getFacade();

		$id = $pheanstalk
			->useTube('testpeekburied')
			->watch('testpeekburied')
			->ignore('default')
			->put('test');

		$job = $pheanstalk->reserve($id);
		$pheanstalk->bury($job);

		$job = $pheanstalk->peekBuried();

		$this->assertEqual($job->getData(), 'test');
	}

	public function testStatsJob()
	{
		$pheanstalk = $this->_getFacade();

		$id = $pheanstalk
			->useTube('teststatsjob')
			->watch('teststatsjob')
			->ignore('default')
			->put('test');

		$stats = $pheanstalk->statsJob($id);

		$this->assertEqual($stats->id, $id);
		$this->assertEqual($stats->tube, 'teststatsjob');
		$this->assertEqual($stats->state, 'ready');
		$this->assertEqual($stats->pri, Pheanstalk::DEFAULT_PRIORITY);
		$this->assertEqual($stats->delay, Pheanstalk::DEFAULT_DELAY);
		$this->assertEqual($stats->ttr, Pheanstalk::DEFAULT_TTR);
		$this->assertEqual($stats->timeouts, 0);
		$this->assertEqual($stats->releases, 0);
		$this->assertEqual($stats->buries, 0);
		$this->assertEqual($stats->kicks, 0);
	}

	public function testStatsJobWithJobObject()
	{
		$pheanstalk = $this->_getFacade();

		$pheanstalk
			->useTube('teststatsjobwithjobobject')
			->watch('teststatsjobwithjobobject')
			->ignore('default')
			->put('test');

		$job = $pheanstalk
			->reserve();

		$stats = $pheanstalk->statsJob($job);

		$this->assertEqual($stats->id, $job->getId());
		$this->assertEqual($stats->tube, 'teststatsjobwithjobobject');
		$this->assertEqual($stats->state, 'reserved');
		$this->assertEqual($stats->pri, Pheanstalk::DEFAULT_PRIORITY);
		$this->assertEqual($stats->delay, Pheanstalk::DEFAULT_DELAY);
		$this->assertEqual($stats->ttr, Pheanstalk::DEFAULT_TTR);
		$this->assertEqual($stats->timeouts, 0);
		$this->assertEqual($stats->releases, 0);
		$this->assertEqual($stats->buries, 0);
		$this->assertEqual($stats->kicks, 0);
	}

	public function testStatsTube()
	{
		$pheanstalk = $this->_getFacade();

		$tube = 'test-stats-tube';
		$pheanstalk->useTube($tube);

		$stats = $pheanstalk->statsTube($tube);

		$this->assertEqual($stats->name, $tube);
		$this->assertEqual($stats->current_jobs_reserved, '0');
	}

	public function testStats()
	{
		$pheanstalk = $this->_getFacade();

		$stats = $pheanstalk->useTube('test-stats')->stats();

		$properties = array('pid', 'cmd_put', 'cmd_stats_job');
		foreach ($properties as $property)
		{
			$this->assertTrue(
				isset($stats->$property),
				"property $property should exist"
			);
		}

		$this->assertTrue($stats->pid > 0, 'stats should have pid > 0');
		$this->assertTrue($stats->cmd_use > 0, 'stats should have cmd_use > 0');
	}

	public function testPauseTube()
	{
		$tube = 'test-pause-tube';
		$pheanstalk = $this->_getFacade();

		$pheanstalk
			->useTube($tube)
			->watch($tube)
			->ignore('default')
			->put(__METHOD__);

		$response = $pheanstalk
			->pauseTube($tube, 1)
			->reserve(0);

		$this->assertIdentical($response, false);
	}

	// ----------------------------------------
	// private

	private function _getFacade()
	{
		return new Pheanstalk(self::SERVER_HOST);
	}
}

