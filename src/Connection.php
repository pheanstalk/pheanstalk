<?php

namespace Pheanstalk;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Socket\NativeSocket;

/**
 * A connection to a beanstalkd server.
 *
 * @author  Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class Connection
{
    const CRLF = "\r\n";
    const CRLF_LENGTH = 2;
    const DEFAULT_CONNECT_TIMEOUT = 2;

    // responses which are global errors, mapped to their exception short-names
    private static $_errorResponses = array(
        ResponseInterface::RESPONSE_OUT_OF_MEMORY   => 'OutOfMemory',
        ResponseInterface::RESPONSE_INTERNAL_ERROR  => 'InternalError',
        ResponseInterface::RESPONSE_DRAINING        => 'Draining',
        ResponseInterface::RESPONSE_BAD_FORMAT      => 'BadFormat',
        ResponseInterface::RESPONSE_UNKNOWN_COMMAND => 'UnknownCommand',
    );

    // responses which are followed by data
    private static $_dataResponses = array(
        ResponseInterface::RESPONSE_RESERVED,
        ResponseInterface::RESPONSE_FOUND,
        ResponseInterface::RESPONSE_OK,
    );

    private $socket;
    private $hostname;
    private $port;

    /**
     * @var int
     */
    private $connectTimeout;

    /**
     * @param string $hostname
     * @param int    $port
     * @param float  $connectTimeout
     * @param bool   $connectPersistent
     */
    public function __construct($hostname, $port, ?int $connectTimeout = null)
    {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->connectTimeout = $connectTimeout ?? self::DEFAULT_CONNECT_TIMEOUT;
    }

    /**
     * Sets a manually created socket, used for unit testing.
     *
     * @param SocketInterface $socket
     *
     * @return $this
     */
    public function setSocket(SocketInterface $socket)
    {
        $this->socket = $socket;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasSocket()
    {
        return isset($this->socket);
    }

    /**
     * Disconnect the socket.
     * Subsequent socket operations will create a new connection.
     */
    public function disconnect()
    {
        $this->getSocket()->disconnect();
        $this->socket = null;
    }

    /**
     * @param object $command Command
     *
     * @throws Exception\ClientException
     *
     * @return object Response
     */
    public function dispatchCommand($command)
    {
        $socket = $this->getSocket();

        $to_send = $command->getCommandLine().self::CRLF;

        if ($command->hasData()) {
            $to_send .= $command->getData().self::CRLF;
        }

        $socket->write($to_send);

        $responseLine = $socket->getLine();
        $responseName = preg_replace('#^(\S+).*$#s', '$1', $responseLine);

        if (isset(self::$_errorResponses[$responseName])) {
            $exception = sprintf(
                '\Pheanstalk\Exception\Server%sException',
                self::$_errorResponses[$responseName]
            );

            throw new $exception(sprintf(
                "%s in response to '%s'",
                $responseName,
                $command
            ));
        }

        if (in_array($responseName, self::$_dataResponses)) {
            $dataLength = preg_replace('#^.*\b(\d+)$#', '$1', $responseLine);
            $data = $socket->read($dataLength);

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

    /**
     * Returns the connect timeout for this connection.
     *
     * @return float
     */
    public function getConnectTimeout()
    {
        return $this->connectTimeout;
    }

    /**
     * Returns the host for this connection.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->hostname;
    }

    /**
     * Returns the port for this connection.
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
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
            $this->socket = new NativeSocket(
                $this->hostname,
                $this->port,
                $this->connectTimeout
            );
        }

        return $this->socket;
    }

    /**
     * Checks connection to the beanstalkd socket.
     *
     * @return true|false
     */
    public function isServiceListening()
    {
        try {
            $this->getSocket();
            return true;
        } catch (Exception\ConnectionException $e) {
            return false;
        }
    }
}
