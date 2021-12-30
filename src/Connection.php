<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\CommandWithDataInterface;
use Pheanstalk\Contract\DataLengthProviderInterface;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\MalformedResponseException;
use Pheanstalk\Exception\ServerBadFormatException;
use Pheanstalk\Exception\ServerInternalErrorException;
use Pheanstalk\Exception\ServerOutOfMemoryException;
use Pheanstalk\Exception\ServerUnknownCommandException;

/**
 * A connection to a beanstalkd server, backed by any type of socket.
 *
 */
class Connection
{
    private const CRLF = "\r\n";
    private const CRLF_LENGTH = 2;

    private SocketInterface|null $socket;
    public function __construct(
        private readonly SocketFactoryInterface $factory
    ) {
    }

    /**
     * Connect the socket, this is done automatically when dispatching commands
     */
    public function connect(): void
    {
        $this->getSocket();
    }

    /**
     * Disconnect the socket.
     * Subsequent socket operations will create a new connection.
     */
    public function disconnect(): void
    {
        if (isset($this->socket)) {
            $this->socket->disconnect();
            $this->socket = null;
        }
    }

    /**
     * @param int<0, max> $length
     */
    private function readData(SocketInterface $socket, int $length): string
    {
        $result = $socket->read($length);
        if ($socket->read(self::CRLF_LENGTH) !== self::CRLF) {
            throw new Exception\ClientException(sprintf(
                'Expected %u bytes of CRLF after %u bytes of data',
                self::CRLF_LENGTH,
                $length
            ));
        }
        return $result;
    }


    private function sendCommand(CommandInterface $command): void
    {
        $socket = $this->getSocket();
        $buffer = $command->getCommandLine() . self::CRLF;
        if ($command instanceof CommandWithDataInterface) {
            $buffer .= $command->getData() . self::CRLF;
        }

        $socket->write($buffer);
    }

    private function readRawResponse(): RawResponse
    {
        $socket = $this->getSocket();

        // This is always a simple line consisting of a response type name and 0 - 2 optional numerical arguments.
        $responseLine = $socket->getLine();


        $responseParts = explode(' ', $responseLine);
        // count($responseParts) == 1|2|3

        $responseType = ResponseType::from(array_shift($responseParts));
        // count($responseParts) == 1|2

        if ($responseType->hasData()) {
            $dataLength = (int) array_pop($responseParts);
            if ($dataLength < 0) {
                throw MalformedResponseException::negativeDataLength();
            }
            $data = $this->readData($socket, $dataLength);
        }
        // count($responseParts) = 0|1

        return match ($responseType) {
            ResponseType::OutOfMemory => throw new ServerOutOfMemoryException(),
            ResponseType::InternalError => throw new ServerInternalErrorException(),
            ResponseType::BadFormat => throw new ServerBadFormatException(),
            ResponseType::UnknownCommand => throw new ServerUnknownCommandException(),
            default => new RawResponse($responseType, array_pop($responseParts), $data ?? null)
        };
    }

    public function dispatchCommand(CommandInterface $command): RawResponse
    {
        $this->sendCommand($command);
        return $this->readRawResponse();
    }

    /**
     * Socket handle for the connection to beanstalkd.
     * @throws Exception\ConnectionException
     */
    private function getSocket(): SocketInterface
    {
        if (!isset($this->socket)) {
            $this->socket = $this->factory->create();
        }

        return $this->socket;
    }
}
