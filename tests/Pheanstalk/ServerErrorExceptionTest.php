<?php

namespace Pheanstalk;

use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests exceptions thrown to represent non-command-specific error responses.
 */
class ServerErrorExceptionTest extends TestCase
{
    private $command;

    public function setUp()
    {
        $this->command = new Command\UseCommand('tube5');
    }

    /**
     * A connection with a mock socket, configured to return the given line.
     */
    private function connection(string $line): Connection
    {
        $socket = $this->getMockBuilder(\Pheanstalk\Contract\SocketInterface::class)
            ->getMock();

        $socket->expects($this->any())
            ->method('getLine')
            ->will($this->returnValue($line));

        $connection = new Connection(new class($socket) implements SocketFactoryInterface {
            private $socket;
            public function __construct($socket)
            {
                $this->socket = $socket;
            }

            public function create(): SocketInterface
            {
                return $this->socket;
            }
        });
        return $connection;
    }

    /**
     * @expectedException \Pheanstalk\Exception\ServerOutOfMemoryException
     */
    public function testCommandsHandleOutOfMemory()
    {
        $this->connection('OUT_OF_MEMORY')->dispatchCommand($this->command);
    }

    /**
     * @expectedException \Pheanstalk\Exception\ServerInternalErrorException
     */
    public function testCommandsHandleInternalError()
    {
        $this->connection('INTERNAL_ERROR')->dispatchCommand($this->command);
    }

    /**
     * @expectedException \Pheanstalk\Exception\ServerDrainingException
     */
    public function testCommandsHandleDraining()
    {
        $this->connection('DRAINING')->dispatchCommand($this->command);
    }

    /**
     * @expectedException \Pheanstalk\Exception\ServerBadFormatException
     */
    public function testCommandsHandleBadFormat()
    {
        $this->connection('BAD_FORMAT')->dispatchCommand($this->command);
    }

    /**
     * @expectedException \Pheanstalk\Exception\ServerUnknownCommandException
     */
    public function testCommandsHandleUnknownCommand()
    {
        $this->connection('UNKNOWN_COMMAND')->dispatchCommand($this->command);
    }
}
