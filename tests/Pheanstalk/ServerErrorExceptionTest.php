<?php

declare(strict_types=1);

namespace Pheanstalk\Tests;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\ServerBadFormatException;
use Pheanstalk\Exception\ServerInternalErrorException;
use Pheanstalk\Exception\ServerOutOfMemoryException;
use Pheanstalk\Exception\ServerUnknownCommandException;
use PHPUnit\Framework\TestCase;

/**
 * Tests exceptions thrown to represent non-command-specific error responses.
 */
class ServerErrorExceptionTest extends TestCase
{
    private CommandInterface $command;

    protected function setUp(): void
    {
        $this->command = new Command\UseCommand(new TubeName('tube5'));
    }

    /**
     * A connection with a mock socket, configured to return the given line.
     */
    private function connection(string $line): Connection
    {
        $socket = $this->getMockBuilder(SocketInterface::class)
            ->getMock();

        $socket->expects(self::any())
            ->method('getLine')
            ->will(self::returnValue($line));

        return new Connection(new class($socket) implements SocketFactoryInterface {
            public function __construct(private readonly SocketInterface $socket)
            {
            }

            public function create(): SocketInterface
            {
                return $this->socket;
            }
        });
    }

    public function testCommandsHandleOutOfMemory()
    {
        $this->expectException(ServerOutOfMemoryException::class);
        $this->connection('OUT_OF_MEMORY')->dispatchCommand($this->command);
    }

    public function testCommandsHandleInternalError()
    {
        $this->expectException(ServerInternalErrorException::class);
        $this->connection('INTERNAL_ERROR')->dispatchCommand($this->command);
    }

    public function testCommandsHandleBadFormat()
    {
        $this->expectException(ServerBadFormatException::class);
        $this->connection('BAD_FORMAT')->dispatchCommand($this->command);
    }

    public function testCommandsHandleUnknownCommand()
    {
        $this->expectException(ServerUnknownCommandException::class);
        $this->connection('UNKNOWN_COMMAND')->dispatchCommand($this->command);
    }
}
