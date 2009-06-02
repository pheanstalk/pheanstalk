<?php

Mock::generate('Pheanstalk_Job', 'MockJob');

/**
 * Tests exceptions are thrown correctly by Pheanstalk_Command implementations.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_CommandExceptionTest
	extends UnitTestCase
{
	public function testDeleteNotFound()
	{
		$command = new Pheanstalk_Command_DeleteCommand($this->_mockJob(5));
		$this->expectException('Pheanstalk_Exception_ServerException');
		$command->parseResponse('NOT_FOUND', null);
	}

	// ----------------------------------------

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
