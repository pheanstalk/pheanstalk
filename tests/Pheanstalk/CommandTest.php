<?php

Mock::generate('Pheanstalk_Job', 'MockJob');

/**
 * Tests for Pheanstalk_Command implementations.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_CommandTest
	extends UnitTestCase
{
	public function testBury()
	{
		$command = new Pheanstalk_Command_BuryCommand($this->_mockJob(5), 2);
		$this->_assertCommandLine($command, 'bury 5 2');

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('BURIED', null),
			Pheanstalk_Response::RESPONSE_BURIED
		);
	}

	public function testDelete()
	{
		$command = new Pheanstalk_Command_DeleteCommand($this->_mockJob(5));
		$this->_assertCommandLine($command, 'delete 5');

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('DELETED', null),
			Pheanstalk_Response::RESPONSE_DELETED
		);
	}

	public function testIgnore()
	{
		$command = new Pheanstalk_Command_IgnoreCommand('tube1');
		$this->_assertCommandLine($command, 'ignore tube1');

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('WATCHING 2', null),
			Pheanstalk_Response::RESPONSE_WATCHING,
			array('count' => 2)
		);
	}

	public function testKick()
	{
		$command = new Pheanstalk_Command_KickCommand(5);
		$this->_assertCommandLine($command, 'kick 5');

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('KICKED 2', null),
			Pheanstalk_Response::RESPONSE_KICKED,
			array('kicked' => 2)
		);
	}

	public function testListTubesWatched()
	{
		$command = new Pheanstalk_Command_ListTubesWatchedCommand();
		$this->_assertCommandLine($command, 'list-tubes-watched');

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('OK 16', "---\n- one\n- two\n"),
			Pheanstalk_Response::RESPONSE_OK,
			array('one', 'two')
		);
	}

	public function testListTubeUsed()
	{
		$command = new Pheanstalk_Command_ListTubeUsedCommand();
		$this->_assertCommandLine($command, 'list-tube-used');

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('USING default', null),
			Pheanstalk_Response::RESPONSE_USING,
			array('tube' => 'default')
		);
	}

	public function testPut()
	{
		$command = new Pheanstalk_Command_PutCommand('data', 5, 6, 7);
		$this->_assertCommandLine($command, 'put 5 6 7 4', true);
		$this->assertEqual($command->getData(), 'data');

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('INSERTED 4', null),
			Pheanstalk_Response::RESPONSE_INSERTED,
			array('id' => '4')
		);
	}

	public function testRelease()
	{
		$job = $this->_mockJob(3);
		$command = new Pheanstalk_Command_ReleaseCommand($job, 1, 0);
		$this->_assertCommandLine($command, 'release 3 1 0');

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('RELEASED', null),
			Pheanstalk_Response::RESPONSE_RELEASED
		);
	}

	public function testReserve()
	{
		$command = new Pheanstalk_Command_ReserveCommand();
		$this->_assertCommandLine($command, 'reserve');

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('RESERVED 5 9', "test data"),
			Pheanstalk_Response::RESPONSE_RESERVED,
			array('id' => 5, 'jobdata' => 'test data')
		);
	}

	public function testUse()
	{
		$command = new Pheanstalk_Command_UseCommand('tube5');
		$this->_assertCommandLine($command, 'use tube5');

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('USING tube5', null),
			Pheanstalk_Response::RESPONSE_USING,
			array('tube' => 'tube5')
		);
	}

	public function testWatch()
	{
		$command = new Pheanstalk_Command_WatchCommand('tube6');
		$this->_assertCommandLine($command, 'watch tube6');

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('WATCHING 3', null),
			Pheanstalk_Response::RESPONSE_WATCHING,
			array('count' => '3')
		);
	}

	public function testReserveWithTimeout()
	{
		$command = new Pheanstalk_Command_ReserveCommand(10);
		$this->_assertCommandLine($command, 'reserve-with-timeout 10');

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('TIMED_OUT', null),
			Pheanstalk_Response::RESPONSE_TIMED_OUT
		);
	}

	public function testTouch()
	{
		$command = new Pheanstalk_Command_TouchCommand($this->_mockJob(5));
		$this->_assertCommandLine($command, 'touch 5');

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('TOUCHED', null),
			Pheanstalk_Response::RESPONSE_TOUCHED
		);
	}

	public function testListTubes()
	{
		$command = new Pheanstalk_Command_ListTubesCommand();
		$this->_assertCommandLine($command, 'list-tubes');

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('OK 16', "---\n- one\n- two\n"),
			Pheanstalk_Response::RESPONSE_OK,
			array('one', 'two')
		);
	}

	public function testPeek()
	{
		$command = new Pheanstalk_Command_PeekCommand(5);
		$this->_assertCommandLine($command, 'peek 5');

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('FOUND 5 9', "test data"),
			Pheanstalk_Response::RESPONSE_FOUND,
			array('id' => 5, 'jobdata' => 'test data')
		);
	}

	public function testPeekReady()
	{
		$command = new Pheanstalk_Command_PeekCommand('ready');
		$this->_assertCommandLine($command, 'peek-ready');
	}

	public function testPeekDelayed()
	{
		$command = new Pheanstalk_Command_PeekCommand('delayed');
		$this->_assertCommandLine($command, 'peek-delayed');
	}

	public function testPeekBuried()
	{
		$command = new Pheanstalk_Command_PeekCommand('buried');
		$this->_assertCommandLine($command, 'peek-buried');
	}

	public function testStatsJob()
	{
		$command = new Pheanstalk_Command_StatsJobCommand(5);
		$this->_assertCommandLine($command, 'stats-job 5');

		$data = "---\r\nid: 8\r\ntube: test\r\nstate: delayed\r\n";

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('OK '.strlen($data), $data),
			Pheanstalk_Response::RESPONSE_OK,
			array('id' => '8', 'tube' => 'test', 'state' => 'delayed')
		);
	}

	public function testStatsTube()
	{
		$command = new Pheanstalk_Command_StatsTubeCommand('test');
		$this->_assertCommandLine($command, 'stats-tube test');

		$data = "---\r\nname: test\r\ncurrent-jobs-ready: 5\r\n";

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('OK '.strlen($data), $data),
			Pheanstalk_Response::RESPONSE_OK,
			array('name' => 'test', 'current-jobs-ready' => '5')
		);
	}

	public function testStats()
	{
		$command = new Pheanstalk_Command_StatsCommand();
		$this->_assertCommandLine($command, 'stats');

		$data = "---\r\npid: 123\r\nversion: 1.3\r\n";

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('OK '.strlen($data), $data),
			Pheanstalk_Response::RESPONSE_OK,
			array('pid' => '123', 'version' => '1.3')
		);
	}

	public function testPauseTube()
	{
		$command = new Pheanstalk_Command_PauseTubeCommand('testtube7', 10);
		$this->_assertCommandLine($command, 'pause-tube testtube7 10');
		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('PAUSED', null),
			Pheanstalk_Response::RESPONSE_PAUSED
		);
	}

	// ----------------------------------------

	/**
	 * @param Pheanstalk_Command
	 * @param string $expected
	 */
	private function _assertCommandLine($command, $expected, $expectData = false)
	{
		$this->assertEqual($command->getCommandLine(), $expected);

		if ($expectData)
			$this->assertTrue($command->hasData(), 'should have data');
		else
			$this->assertFalse($command->hasData(), 'should have no data');
	}

	/**
	 * @param Pheanstalk_Response $response
	 * @param string $expectName
	 */
	private function _assertResponse($response, $expectName, $data = array())
	{
		$this->assertEqual($response->getResponseName(), $expectName);
		$this->assertEqual($response->getArrayCopy(), $data);
	}

	/**
	 * @param int $id
	 */
	private function _mockJob($id)
	{
		$job = new MockJob();
		$job->setReturnValue('getId', $id);
		return $job;
	}
}
