<?php

namespace Pheanstalk;

/**
 * Tests exceptions thrown to represent non-command-specific error responses.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class ServerErrorExceptionTest extends \PHPUnit_Framework_TestCase
{
    private $_command;

    public function setUp()
    {
        $this->_command = new Command\UseCommand('tube5');
    }

    /**
     * A connection with a mock socket, configured to return the given line.
     * @return Connection
     */
    private function _connection($line)
    {
        $socket = $this->getMockBuilder('\Pheanstalk\Socket')
            ->disableOriginalConstructor()
            ->getMock();

        $socket->expects($this->any())
            ->method('getLine')
            ->will($this->returnValue($line));

        $connection = new Connection(null, null);
        $connection->setSocket($socket);

        return $connection;
    }

    /**
     * @expectedException \Pheanstalk\Exception\ServerOutOfMemoryException
     */
    public function testCommandsHandleOutOfMemory()
    {
        $this->_connection('OUT_OF_MEMORY')->dispatchCommand($this->_command);
    }

    /**
     * @expectedException \Pheanstalk\Exception\ServerInternalErrorException
     */
    public function testCommandsHandleInternalError()
    {
        $this->_connection('INTERNAL_ERROR')->dispatchCommand($this->_command);
    }

    /**
     * @expectedException \Pheanstalk\Exception\ServerDrainingException
     */
    public function testCommandsHandleDraining()
    {
        $this->_connection('DRAINING')->dispatchCommand($this->_command);
    }

    /**
     * @expectedException \Pheanstalk\Exception\ServerBadFormatException
     */
    public function testCommandsHandleBadFormat()
    {
        $this->_connection('BAD_FORMAT')->dispatchCommand($this->_command);
    }

    /**
     * @expectedException \Pheanstalk\Exception\ServerUnknownCommandException
     */
    public function testCommandsHandleUnknownCommand()
    {
        $this->_connection('UNKNOWN_COMMAND')->dispatchCommand($this->_command);
    }
}
