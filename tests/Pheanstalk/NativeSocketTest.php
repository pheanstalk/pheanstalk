<?php

/**
 * Tests the Pheanstalk NativeSocket class
 *
 * @author SlNpacifist
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_NativeSocketTest
	extends UnitTestCase
{
	private $_streamFunctions;

	public function setUp()
	{
		Mock::generate('Pheanstalk_Socket_StreamFunctions', 'MockStreamFunctions');

		$instance = new MockStreamFunctions();
		$instance->setReturnValue('fsockopen', true);

		Pheanstalk_Socket_StreamFunctions::setInstance($instance);
		$this->_streamFunctions = $instance;
	}

	public function tearDown()
	{
		Pheanstalk_Socket_StreamFunctions::unsetInstance();
	}

	public function testWrite()
	{
		$this->_streamFunctions->setReturnValue('fwrite', false);

		$this->expectException('Pheanstalk_Exception_SocketException',
			'Write should throw an exception if fwrite returns false');

		$socket = new Pheanstalk_Socket_NativeSocket('host', 1024, 0);
		$socket->write('data');
	}

	public function testRead()
	{
		$this->_streamFunctions->setReturnValue('fread', false);

		$this->expectException('Pheanstalk_Exception_SocketException',
			'Read should throw an exception if fread returns false');

		$socket = new Pheanstalk_Socket_NativeSocket('host', 1024, 0);
		$socket->read(1);
	}
}
