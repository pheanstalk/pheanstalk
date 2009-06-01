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
			$command->parseResponse('BURIED', null),
			Pheanstalk_Response::RESPONSE_BURIED
		);
	}

	public function testDelete()
	{
		$command = new Pheanstalk_Command_DeleteCommand($this->_mockJob(5));
		$this->_assertCommandLine($command, 'delete 5');

		$this->_assertResponse(
			$command->parseResponse('DELETED', null),
			Pheanstalk_Response::RESPONSE_DELETED
		);
	}

	public function testIgnore()
	{
		$command = new Pheanstalk_Command_IgnoreCommand('tube1');
		$this->_assertCommandLine($command, 'ignore tube1');

		$this->_assertResponse(
			$command->parseResponse('WATCHING 2', null),
			Pheanstalk_Response::RESPONSE_WATCHING,
			array('count' => 2)
		);
	}

	public function testKick()
	{
		$command = new Pheanstalk_Command_KickCommand(5);
		$this->_assertCommandLine($command, 'kick 5');

		$this->_assertResponse(
			$command->parseResponse('KICKED 2', null),
			Pheanstalk_Response::RESPONSE_KICKED,
			array('kicked' => 2)
		);
	}

	public function testListTubesWatched()
	{
		$command = new Pheanstalk_Command_ListTubesWatchedCommand();
		$this->_assertCommandLine($command, 'list-tubes-watched');

		$this->_assertResponse(
			$command->parseResponse('OK 16', "---\n- one\n- two\n"),
			Pheanstalk_Response::RESPONSE_OK,
			array('tubes' => array('one', 'two'))
		);
	}

	public function testListTubeUsed()
	{
		$command = new Pheanstalk_Command_ListTubeUsedCommand();
		$this->_assertCommandLine($command, 'list-tube-used');

		$this->_assertResponse(
			$command->parseResponse('USING default', null),
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
			$command->parseResponse('INSERTED 4', null),
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
			$command->parseResponse('RELEASED', null),
			Pheanstalk_Response::RESPONSE_RELEASED
		);
	}

	public function testReserve()
	{
		$command = new Pheanstalk_Command_ReserveCommand();
		$this->_assertCommandLine($command, 'reserve');

		$this->_assertResponse(
			$command->parseResponse('RESERVED 5 9', "test data"),
			Pheanstalk_Response::RESPONSE_RESERVED,
			array('id' => 5, 'jobdata' => 'test data')
		);
	}

	public function testUse()
	{
		$command = new Pheanstalk_Command_UseCommand('tube5');
		$this->_assertCommandLine($command, 'use tube5');

		$this->_assertResponse(
			$command->parseResponse('USING tube5', null),
			Pheanstalk_Response::RESPONSE_USING,
			array('tube' => 'tube5')
		);
	}

	public function testWatch()
	{
		$command = new Pheanstalk_Command_WatchCommand('tube6');
		$this->_assertCommandLine($command, 'watch tube6');

		$this->_assertResponse(
			$command->parseResponse('WATCHING 3', null),
			Pheanstalk_Response::RESPONSE_WATCHING,
			array('count' => '3')
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
