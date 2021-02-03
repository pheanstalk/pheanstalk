<?php

namespace Pheanstalk\Tests;

use Pheanstalk\Command\UseCommand;
use Pheanstalk\Connection;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\ServerBadFormatException;
use Pheanstalk\Exception\ServerDrainingException;
use Pheanstalk\Exception\ServerInternalErrorException;
use Pheanstalk\Exception\ServerOutOfMemoryException;
use Pheanstalk\Exception\ServerUnknownCommandException;

/**
 * Tests exceptions thrown to represent non-command-specific error responses.
 */
class ServerErrorExceptionTest extends BaseTestCase
{
    /**
     * A connection with a mock socket, configured to return the given line.
     */
    private function connection(string $line): Connection
    {
        $socket = $this->getMockBuilder(SocketInterface::class)
            ->getMock();

        $socket->expects(self::any())
            ->method('getLine')
            ->willReturn($line);

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

    public function testCommandsHandleOutOfMemory()
    {
        $this->expectException(ServerOutOfMemoryException::class);
        $this->connection('OUT_OF_MEMORY')->dispatchCommand(new UseCommand('tube5'));
    }

    public function testCommandsHandleInternalError()
    {
        $this->expectException(ServerInternalErrorException::class);
        $this->connection('INTERNAL_ERROR')->dispatchCommand(new UseCommand('tube5'));
    }

    public function testCommandsHandleDraining()
    {
        $this->expectException(ServerDrainingException::class);
        $this->connection('DRAINING')->dispatchCommand(new UseCommand('tube5'));
    }

    public function testCommandsHandleBadFormat()
    {
        $this->expectException(ServerBadFormatException::class);
        $this->connection('BAD_FORMAT')->dispatchCommand(new UseCommand('tube5'));
    }

    public function testCommandsHandleUnknownCommand()
    {
        $this->expectException(ServerUnknownCommandException::class);
        $this->connection('UNKNOWN_COMMAND')->dispatchCommand(new UseCommand('tube5'));
    }
}
