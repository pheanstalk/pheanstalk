<?php

/**
 * Tests for reported/discovered issues & bugs.
 * Relies on a running beanstalkd server.
 * @see http://github.com/pda/pheanstalk/issues
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_BugfixConnectionTest
	extends UnitTestCase
{
	const SERVER_HOST = 'localhost';

	/**
	 * Issue: NativeSocket's read() doesn't work with jobs larger than 8192 bytes
	 * @see http://github.com/pda/pheanstalk/issues/4
  	 *
	 * PHP 5.2.10-2ubuntu6.4 reads nearly double that on the first fread().
	 * This is probably due to a prior call to fgets() pre-filling the read buffer.
	 */
	public function testIssue4ReadingOver8192Bytes()
	{
		$length = 8192 * 3;

		$pheanstalk = $this->_createPheanstalk();
		$pheanstalk->put(str_repeat('.', $length));
		$job = $pheanstalk->peekReady();
		$this->assertEqual(strlen($job->getData()), $length, 'data length: %s');
	}

	// ----------------------------------------
	// private

	private function _createPheanstalk()
	{
		$pheanstalk = new Pheanstalk(self::SERVER_HOST);
		$tube = preg_replace('#[^a-z]#', '', strtolower(__CLASS__));

		$pheanstalk
			->useTube($tube)
			->watch($tube)
			->ignore('default');

		try { while ($pheanstalk->delete($pheanstalk->peekDelayed())); }
		catch (Pheanstalk_Exception_ServerException $e) {}

		try { while ($pheanstalk->delete($pheanstalk->peekReady())); }
		catch (Pheanstalk_Exception_ServerException $e) {}

		return $pheanstalk;
	}
}

