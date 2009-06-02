<?php

Mock::generate('Pheanstalk_Socket', 'MockSocket');

/**
 * Tests exceptions thrown to represent non-command-specific error responses.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_ServerErrorExceptionTest
	extends UnitTestCase
{
	private $_command;

	public function setUp()
	{
		$this->_command = new Pheanstalk_Command_UseCommand('tube5');
	}

	/**
	 * A connection with a mock socket, configured to return the given line.
	 * @return Pheanstalk_Connection
	 */
	private function _connection($line)
	{
		$socket = new MockSocket();
		$socket->setReturnValue('getLine', $line);

		$connection = new Pheanstalk_Connection(null, null);
		$connection->setSocket($socket);
		return $connection;
	}

	public function testCommandsHandleOutOfMemory()
	{
		$this->expectException('Pheanstalk_Exception_ServerOutOfMemoryException');
		$this->_connection('OUT_OF_MEMORY')->dispatchCommand($this->_command);
	}


	public function testCommandsHandleInternalError()
	{
		$this->expectException('Pheanstalk_Exception_ServerInternalErrorException');
		$this->_connection('INTERNAL_ERROR')->dispatchCommand($this->_command);
	}

	public function testCommandsHandleDraining()
	{
		$this->expectException('Pheanstalk_Exception_ServerDrainingException');
		$this->_connection('DRAINING')->dispatchCommand($this->_command);
	}

	public function testCommandsHandleBadFormat()
	{
		$this->expectException('Pheanstalk_Exception_ServerBadFormatException');
		$this->_connection('BAD_FORMAT')->dispatchCommand($this->_command);
	}

	public function testCommandsHandleUnknownCommand()
	{
		$this->expectException('Pheanstalk_Exception_ServerUnknownCommandException');
		$this->_connection('UNKNOWN_COMMAND')->dispatchCommand($this->_command);
	}
}
