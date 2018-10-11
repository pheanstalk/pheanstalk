<?php

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
    const CRLF = "\r\n";
    const CRLF_LENGTH = 2;
    const DEFAULT_CONNECT_TIMEOUT = 2;

    // responses which are global errors, mapped to their exception classes
    private static $errorResponses = [
        ResponseInterface::RESPONSE_OUT_OF_MEMORY   => ServerOutOfMemoryException::class,
        ResponseInterface::RESPONSE_INTERNAL_ERROR  => ServerInternalErrorException::class,
        ResponseInterface::RESPONSE_DRAINING        => ServerDrainingException::class,
        ResponseInterface::RESPONSE_BAD_FORMAT      => ServerBadFormatException::class,
        ResponseInterface::RESPONSE_UNKNOWN_COMMAND => ServerUnknownCommandException::class,
    ];

    // responses which are followed by data
    private static $dataResponses = [
        ResponseInterface::RESPONSE_RESERVED,
        ResponseInterface::RESPONSE_FOUND,
        ResponseInterface::RESPONSE_OK,
    ];

    /**
     * @var SocketFactoryInterface
     */
    private $factory;

    /**
     * @var ?SocketInterface
     */
    private $socket;

    public function __construct(SocketFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Disconnect the socket.
     * Subsequent socket operations will create a new connection.
     */
    public function disconnect()
    {
        if (isset($this->socket)) {
            $this->socket->disconnect();
            $this->socket = null;
        }
    }

    /**
     * @throws Exception\ClientException
     */
    public function dispatchCommand(CommandInterface $command): ArrayResponse
    {
        $socket = $this->getSocket();

        $to_send = $command->getCommandLine().self::CRLF;

        if ($command->hasData()) {
            $to_send .= $command->getData().self::CRLF;
        }

        $socket->write($to_send);

        $responseLine = $socket->getLine();
        $responseName = preg_replace('#^(\S+).*$#s', '$1', $responseLine);

        if (isset(self::$errorResponses[$responseName])) {
            $exceptionClass = self::$errorResponses[$responseName];

            throw new $exceptionClass(sprintf(
                "%s in response to '%s'",
                $responseName,
                $command->getCommandLine()
            ));
        }

        if (in_array($responseName, self::$dataResponses)) {
            $dataLength = preg_replace('#^.*\b(\d+)$#', '$1', $responseLine);
            $data = $socket->read((int) $dataLength);
            $crlf = $socket->read(self::CRLF_LENGTH);
            if ($crlf !== self::CRLF) {
                throw new Exception\ClientException(sprintf(
                    'Expected %u bytes of CRLF after %u bytes of data',
                    self::CRLF_LENGTH,
                    $dataLength
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
     * @return SocketInterface
     */
    private function getSocket()
    {
        if (!isset($this->socket)) {
            $this->socket = $this->factory->create();
        }

        return $this->socket;
    }
}
