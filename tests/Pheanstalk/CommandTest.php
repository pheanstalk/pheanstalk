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
		$this->_assertCommandLine(
			new Pheanstalk_Command_BuryCommand($this->_mockJob(5), 2),
			'bury 5 2'
		);
	}

	public function testDelete()
	{
		$this->_assertCommandLine(
			new Pheanstalk_Command_DeleteCommand($this->_mockJob(5)),
			'delete 5'
		);
	}

	public function testIgnore()
	{
		$this->_assertCommandLine(
			new Pheanstalk_Command_IgnoreCommand('tube1'),
			'ignore tube1'
		);
	}

	public function testKick()
	{
		$this->_assertCommandLine(
			new Pheanstalk_Command_KickCommand(5),
			'kick 5'
		);
	}

	public function testListTubesWatched()
	{
		$this->_assertCommandLine(
			new Pheanstalk_Command_ListTubesWatchedCommand(),
			'list-tubes-watched'
		);
	}

	public function testListTubeUsed()
	{
		$this->_assertCommandLine(
			new Pheanstalk_Command_ListTubeUsedCommand(),
			'list-tube-used'
		);
	}

	public function testPut()
	{
		$command = new Pheanstalk_Command_PutCommand('data', 5, 6, 7);

		$this->_assertCommandLine(
			$command,
			'put 5 6 7 4',
			true
		);

		$this->assertEqual($command->getData(), 'data');
	}

	public function testRelease()
	{
		$this->_assertCommandLine(
			new Pheanstalk_Command_ReleaseCommand($this->_mockJob(3), 1, 0),
			'release 3 1 0'
		);
	}

	public function testReserve()
	{
		$this->_assertCommandLine(
			new Pheanstalk_Command_ReserveCommand(),
			'reserve'
		);
	}

	public function testUse()
	{
		$this->_assertCommandLine(
			new Pheanstalk_Command_UseCommand('tube5'),
			'use tube5'
		);
	}

	public function testWatch()
	{
		$this->_assertCommandLine(
			new Pheanstalk_Command_WatchCommand('tube6'),
			'watch tube6'
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
	 * @param int $id
	 */
	private function _mockJob($id)
	{
		$job = new MockJob();
		$job->setReturnValue('getId', $id);
		return $job;
	}
}
