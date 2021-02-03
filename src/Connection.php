<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\ServerBadFormatException;
use Pheanstalk\Exception\ServerDrainingException;
use Pheanstalk\Exception\ServerInternalErrorException;
use Pheanstalk\Exception\ServerOutOfMemoryException;
use Pheanstalk\Exception\ServerUnknownCommandException;
use Pheanstalk\Response\ArrayResponse;

/**
 * A connection to a beanstalkd server, backed by any type of socket.
 *
 */
class Connection
{
    private const CRLF = "\r\n";

    // responses which are global errors, mapped to their exception classes
    private static array $errorResponses = [
        ResponseInterface::RESPONSE_OUT_OF_MEMORY   => ServerOutOfMemoryException::class,
        ResponseInterface::RESPONSE_INTERNAL_ERROR  => ServerInternalErrorException::class,
        ResponseInterface::RESPONSE_DRAINING        => ServerDrainingException::class,
        ResponseInterface::RESPONSE_BAD_FORMAT      => ServerBadFormatException::class,
        ResponseInterface::RESPONSE_UNKNOWN_COMMAND => ServerUnknownCommandException::class,
    ];

    private SocketFactoryInterface $factory;

    private SocketInterface|null $socket;

    public function __construct(SocketFactoryInterface $factory)
    {
        $this->factory = $factory;
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
     * @throws Exception\ClientException
     */
    public function dispatchCommand(CommandInterface $command): ResponseInterface
    {
        $socket = $this->getSocket();

        $to_send = $command->getCommandLine() . self::CRLF;

        if ($command->hasData()) {
            $to_send .= $command->getData() . self::CRLF;
        }

        $socket->write($to_send);

        $responseLine = ResponseLine::fromString($socket->getLine());

        if ($responseLine->hasData()) {
            $data = $socket->read($responseLine->getDataLength());
            $crlf = $socket->read(strlen(self::CRLF));
            if ($crlf !== self::CRLF) {
                throw new Exception\ClientException(sprintf(
                    'Expected %u bytes of CRLF after %u bytes of data',
                    strlen(self::CRLF),
                    $responseLine->getDataLength()
                ));
            }
        } else {
            $data = null;
        }

        return $command
            ->getResponseParser()
            ->parseResponse($responseLine, $data);
    }

    // ----------------------------------------

    /**
     * Socket handle for the connection to beanstalkd.
     *
     * @throws Exception\ConnectionException
     *
     */
    private function getSocket(): SocketInterface
    {
        if (!isset($this->socket)) {
            $this->socket = $this->factory->create();
        }

        return $this->socket;
    }
}
