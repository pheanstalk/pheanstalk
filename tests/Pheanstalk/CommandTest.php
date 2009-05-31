<?php

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
	public function testReserve()
	{
		$command = new Pheanstalk_Command_ReserveCommand();
		$this->_assertCommandLine($command, 'reserve');
		$this->_assertNoData($command);
	}

	// ----------------------------------------

	/**
	 * @param Pheanstalk_Command
	 * @param string $expected
	 */
	private function _assertCommandLine($command, $expected)
	{
		$this->assertEqual($command->getCommandLine(), $expected);
	}

	/**
	 * @param Pheanstalk_Command
	 */
	private function _assertNoData($command)
	{
		$this->assertFalse($command->hasData(), 'should have no data');
	}
}
