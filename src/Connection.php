<?php

namespace Pheanstalk;

use Pheanstalk\Socket\NativeSocket;

/**
 * A connection to a beanstalkd server
 *
 * @author Paul Annesley
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
        Response::RESPONSE_OUT_OF_MEMORY => 'OutOfMemory',
        Response::RESPONSE_INTERNAL_ERROR => 'InternalError',
        Response::RESPONSE_DRAINING => 'Draining',
        Response::RESPONSE_BAD_FORMAT => 'BadFormat',
        Response::RESPONSE_UNKNOWN_COMMAND => 'UnknownCommand',
    );

    // responses which are followed by data
    private static $_dataResponses = array(
        Response::RESPONSE_RESERVED,
        Response::RESPONSE_FOUND,
        Response::RESPONSE_OK,
    );

    private $_socket;
    private $_hostname;
    private $_port;
    private $_connectTimeout;
    private $_connectPersistent;

    /**
     * @param string $hostname
     * @param int $port
     * @param float $connectTimeout
     * @param bool $connectPersistent
     */
    public function __construct($hostname, $port, $connectTimeout = null, $connectPersistent = false)
    {
        if (is_null($connectTimeout) || !is_numeric($connectTimeout)) {
            $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT;
        }

        $this->_hostname = $hostname;
        $this->_port = $port;
        $this->_connectTimeout = $connectTimeout;
        $this->_connectPersistent = $connectPersistent;
    }

    /**
     * Sets a manually created socket, used for unit testing.
     *
     * @param Socket $socket
     * @return $this
     */
    public function setSocket(Socket $socket)
    {
        $this->_socket = $socket;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSocket()
    {
        return isset($this->_socket);
    }

    /**
     * Disconnect the socket.
     * Subsequent socket operations will create a new connection.
     */
    public function disconnect()
    {
        $this->_getSocket()->disconnect();
        $this->_socket = null;
    }

    /**
     * @param  object                    $command Command
     * @return object                    Response
     * @throws Exception\ClientException
     */
    public function dispatchCommand($command)
    {
        $socket = $this->_getSocket();

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
        return $this->_connectTimeout;
    }

    /**
     * Returns the host for this connection.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->_hostname;
    }

    /**
     * Returns the port for this connection.
     *
     * @return int
     */
    public function getPort()
    {
        return $this->_port;
    }

    // ----------------------------------------

    /**
     * Socket handle for the connection to beanstalkd
     *
     * @return Socket
     * @throws Exception\ConnectionException
     */
    private function _getSocket()
    {
        if (!isset($this->_socket)) {
            $this->_socket = new NativeSocket(
                $this->_hostname,
                $this->_port,
                $this->_connectTimeout,
                $this->_connectPersistent
            );
        }

        return $this->_socket;
    }

    /**
     * Checks connection to the beanstalkd socket
     *
     * @return true|false
     */
    public function isServiceListening()
    {
        try {
            $this->_getSocket();

            return true;
        } catch (Exception\ConnectionException $e) {
            return false;
        }
    }
}
