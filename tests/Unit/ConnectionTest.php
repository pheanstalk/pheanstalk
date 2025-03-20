<?php

declare(strict_types=1);

namespace Pheanstalk\Tests;

use Pheanstalk\Command\UseCommand;
use Pheanstalk\Connection;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\ConnectionException;
use Pheanstalk\Exception\ServerBadFormatException;
use Pheanstalk\Exception\ServerInternalErrorException;
use Pheanstalk\Exception\ServerOutOfMemoryException;
use Pheanstalk\Exception\ServerUnknownCommandException;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\TubeName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Connection.
 * Relies on a running beanstalkd server.
 */
#[CoversClass(Connection::class)]
final class ConnectionTest extends TestCase
{
    public function testDisconnect(): void
    {
        $socket = $this->getMockBuilder(SocketInterface::class)->getMock();
        $socket->expects(self::once())->method('disconnect');
        $factory = $this->getMockBuilder(SocketFactoryInterface::class)->getMock();
        $factory->expects(self::atLeastOnce())->method('create')->willReturn($socket);

        $connection = new Connection($factory);
        $connection->connect();
        $connection->disconnect();
    }

    public function testReconnectAfterDispatch(): void
    {
        $socket = $this->getMockBuilder(SocketInterface::class)->getMock();
        $socket->expects(self::once())->method('disconnect');
        $socket->expects(self::once())
            ->method('getLine')
            ->willReturn(ResponseType::Using->value);

        $factory = $this->getMockBuilder(SocketFactoryInterface::class)->getMock();
        $factory->expects(self::exactly(2))
            ->method('create')
            ->willReturn($socket);

        $connection = new Connection($factory);
        $connection->connect();
        $connection->disconnect();
        $connection->dispatchCommand(new UseCommand(new TubeName('foo')));
    }

    private function getCommand(): CommandInterface
    {
        return new UseCommand(new TubeName('tube5'));
    }

    /**
     * A connection with a mock socket, configured to return the given line.
     */
    private function getConnection(string $line): Connection
    {
        $socket = $this->getMockBuilder(SocketInterface::class)
            ->getMock();

        $socket->expects(self::once())
            ->method('getLine')
            ->willReturn($line);

        $socket->expects(self::once())->method('write');

        return $this->createConnectionBySocket($socket);
    }

    private function createConnectionBySocket(SocketInterface $socket): Connection
    {
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

    public function testCommandsHandleOutOfMemory(): void
    {
        $this->expectException(ServerOutOfMemoryException::class);
        $this->getConnection(ResponseType::OutOfMemory->value)->dispatchCommand($this->getCommand());
    }

    public function testCommandsHandleInternalError(): void
    {
        $this->expectException(ServerInternalErrorException::class);
        $this->getConnection(ResponseType::InternalError->value)->dispatchCommand($this->getCommand());
    }

    public function testCommandsHandleBadFormat(): void
    {
        $this->expectException(ServerBadFormatException::class);
        $this->getConnection(ResponseType::BadFormat->value)->dispatchCommand($this->getCommand());
    }

    public function testCommandsHandleUnknownCommand(): void
    {
        $this->expectException(ServerUnknownCommandException::class);
        $this->getConnection(ResponseType::UnknownCommand->value)->dispatchCommand($this->getCommand());
    }

    public function testSocketCloseOnIOError(): void
    {
        $socket = $this->getMockBuilder(SocketInterface::class)
            ->getMock();
        $socket->expects(self::exactly(2))
            ->method('write')
            ->willReturnCallback(static function () {
                static $firstRun = true;
                if ($firstRun) {
                    $firstRun = false;
                    throw new ConnectionException(4, 'Interrupted system call');
                }
            });

        $socket->expects(self::once())->method('disconnect');

        $connection = $this->createConnectionBySocket($socket);

        try {
            $connection->dispatchCommand($this->getCommand());
            self::fail('Expected ConnectionException was not thrown');
        } catch (ConnectionException) {
        }
        $socket->expects(self::exactly(1))
            ->method('getLine')
            ->willReturn(ResponseType::Released->value);
        $connection->dispatchCommand($this->getCommand());
    }
}
