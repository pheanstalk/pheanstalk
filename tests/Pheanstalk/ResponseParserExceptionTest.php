<?php

Mock::generate('Pheanstalk_Job', 'MockJob');

/**
 * Tests exceptions thrown by Pheanstalk_ResponseParser implementations.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_ResponseParserExceptionTest
	extends UnitTestCase
{
	public function testDeleteNotFound()
	{
		$this->_expectServerExceptionForResponse(
			new Pheanstalk_Command_DeleteCommand($this->_mockJob(5)),
			'NOT_FOUND'
		);
	}

	public function testReleaseBuried()
	{
		$this->_expectServerExceptionForResponse(
			new Pheanstalk_Command_ReleaseCommand($this->_mockJob(5), 1, 0),
			'BURIED'
		);
	}

	public function testReleaseNotFound()
	{
		$this->_expectServerExceptionForResponse(
			new Pheanstalk_Command_ReleaseCommand($this->_mockJob(5), 1, 0),
			'NOT_FOUND'
		);
	}

	public function testBuryNotFound()
	{
		$this->_expectServerExceptionForResponse(
			new Pheanstalk_Command_BuryCommand($this->_mockJob(5), 1),
			'NOT_FOUND'
		);
	}

	public function testIgnoreNotIgnored()
	{
		$this->_expectServerExceptionForResponse(
			new Pheanstalk_Command_IgnoreCommand('test'),
			'NOT_IGNORED'
		);
	}

	public function testTouchNotFound()
	{
		$this->_expectServerExceptionForResponse(
			new Pheanstalk_Command_TouchCommand($this->_mockJob(5)),
			'NOT_FOUND'
		);
	}

	public function testPeekNotFound()
	{
		$this->_expectServerExceptionForResponse(
			new Pheanstalk_Command_PeekCommand(5),
			'NOT_FOUND'
		);
	}

	public function testPeekInvalidSubject()
	{
		$this->expectException('Pheanstalk_Exception_CommandException');
		new Pheanstalk_Command_PeekCommand('invalid');
	}

	public function testYamlResponseParserNotFound()
	{
		$this->_expectServerExceptionForResponse(
			new Pheanstalk_YamlResponseParser(Pheanstalk_YamlResponseParser::MODE_DICT),
			'NOT_FOUND'
		);
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

	/**
	 * @param Pheanstalk_Command
	 * @param string the response line to parse.
	 */
	private function _expectServerExceptionForResponse($command, $response)
	{
		$this->expectException('Pheanstalk_Exception_ServerException');
		$command->parseResponse($response, null);
	}
}
