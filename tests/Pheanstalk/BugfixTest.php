<?php

/**
 * Tests for reported/discovered issues & bugs which don't fall into
 * an existing category of tests.
 * Does not depend on a running beanstalkd server.
 * @see http://github.com/pda/pheanstalk/issues
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_BugfixTest
	extends UnitTestCase
{
	/**
	 * Issue: Stats() Command fails if Version isn't set
	 * @see http://github.com/pda/pheanstalk/issues/12
	 */
	public function testIssue12YamlParsingMissingValue()
	{
		// missing version number
		$data = "---\r\npid: 123\r\nversion: \r\nkey: value\r\n";

		$command = new Pheanstalk_Command_StatsCommand();

		$this->_assertResponse(
			$command->getResponseParser()->parseResponse('OK '.strlen($data), $data),
			Pheanstalk_Response::RESPONSE_OK,
			array('pid' => '123', 'version' => '', 'key' => 'value')
		);
	}

	// ----------------------------------------
	// private

	/**
	 * @param Pheanstalk_Response $response
	 * @param string $expectName
	 */
	private function _assertResponse($response, $expectName, $data = array())
	{
		$this->assertEqual($response->getResponseName(), $expectName);
		$this->assertEqual($response->getArrayCopy(), $data);
	}
}
