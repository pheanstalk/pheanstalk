<?php

/**
 * Tests the Pheanstalk NativeSocket class
 *
 * @author SlNpacifist
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_ExceptionsTest
	extends UnitTestCase
{
	public function testWrite()
	{
		$this->expectException('Pheanstalk_Exception_SocketException', 'Write should throw an exception if fwrite returns false');
		$socket = new Test_NativeSocket();
		$socket->write(1);
	}

	public function testRead()
	{
		$this->expectException('Pheanstalk_Exception_SocketException', 'Read should throw an exception if fread returns false');
		$socket = new Test_NativeSocket();
		$socket->read(1);
	}
}

class Test_NativeSocket extends Pheanstalk_Socket_NativeSocket
{
	public function __construct()
	{
		$this->_socket = NULL;
	}
}