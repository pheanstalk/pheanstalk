<?php

namespace Pheanstalk;

use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Tests exceptions thrown to represent non-command-specific error responses.
 */
class ServerErrorExceptionTest extends TestCase
{
    private $command;

    public function set_up()
    {
        $this->command = new Command\UseCommand('tube5');
    }

    /**
     * A connection with a mock socket, configured to return the given line.
     *
     * @param string $line
     *
     * @return Connection
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
        $this->expectException(\Pheanstalk\Exception\ServerOutOfMemoryException::class);
        $this->connection('OUT_OF_MEMORY')->dispatchCommand($this->command);
    }

    public function testCommandsHandleInternalError()
    {
        $this->expectException(\Pheanstalk\Exception\ServerInternalErrorException::class);
        $this->connection('INTERNAL_ERROR')->dispatchCommand($this->command);
    }

    public function testCommandsHandleDraining()
    {
        $this->expectException(\Pheanstalk\Exception\ServerDrainingException::class);
        $this->connection('DRAINING')->dispatchCommand($this->command);
    }

    public function testCommandsHandleBadFormat()
    {
        $this->expectException(\Pheanstalk\Exception\ServerBadFormatException::class);
        $this->connection('BAD_FORMAT')->dispatchCommand($this->command);
    }

    public function testCommandsHandleUnknownCommand()
    {
        $this->expectException(\Pheanstalk\Exception\ServerUnknownCommandException::class);
        $this->connection('UNKNOWN_COMMAND')->dispatchCommand($this->command);
    }
}
