<?php

/**
 * A Pheanstalk_Socket implementation around
 * a php sockets extension. Opens a TCP stream socket
 *
 * In case you need to have IPV6 based ip address for the
 * socket server change the AF_INET to AF_INET6
 *
 * Also SOCKET_TIMEOUT could be decreased to fractions of a
 * second for local connections
 *
 * The reason for this socket implementation, mainly is because
 * it successfully closes the socket on the end of the request
 * while  Pheanstalk_Socket_NativeSocket does not
 *
 * NOTE: it requires php-sockets extension enabled
 *
 * @author DataDog <http://www.datadog.lt>
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Socket_SocketsExtensionSocket implements Pheanstalk_Socket
{
    /**
     * The default timeout for a blocking read on the socket
     */
    const SOCKET_TIMEOUT = 1;

    /**
     * Number of retries for attempted writes which return zero length.
     */
    const WRITE_RETRIES = 8;

    private $socket;

    /**
     * @param string $host
     * @param int $port
     */
    public function __construct($host, $port)
    {
        // see http://www.php.net/manual/en/function.socket-create.php
        $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false) {
            throw new Pheanstalk_Exception_SocketException("Failed to open TCP stream socket");
        }
        if (@socket_connect($this->socket, $host, $port) === false) {
            $this->error("Failed to connect socket to {$host}:{$port}");
        }
        if (@socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array(
            'sec' => self::SOCKET_TIMEOUT,
            'usec' => 0
        )) === false) {
            $this->error("Failed to set socket stream timeout option");
        }
    }

    /**
     * Fail with connection error
     *
     * @param string $msg
     * @throws \Pheanstalk_Exception_ConnectionException
     */
    private function error($msg)
    {
        $errmsg = @socket_strerror($errno = socket_last_error($this->socket));
        throw new Pheanstalk_Exception_ConnectionException($errno, "{$errmsg} -> {$msg}");
    }

    /**
     * Closes a socket if it was open
     */
    public function __destruct()
    {
        // close the socket if opened
        if (is_resource($this->socket)) {
            @socket_shutdown($this->socket);
            @socket_close($this->socket);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function write($data)
    {
        $history = new Pheanstalk_Socket_WriteHistory(self::WRITE_RETRIES);

        for ($written = 0, $fwrite = 0; $written < strlen($data); $written += $fwrite) {
            $fwrite = @socket_write($this->socket, substr($data, $written));
            if ($fwrite === false) {
                $this->error("Failed to write buffer to socket");
            }
            $history->log($fwrite);

            if ($history->isFullWithNoWrites()) {
                throw new Pheanstalk_Exception_SocketException(sprintf(
                    'socket_write() failed to write data after %u tries',
                    self::WRITE_RETRIES
                ));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function read($length)
    {
        $read = 0;
        $parts = array();

        while ($read < $length) {
            $data = @socket_read($this->socket, $length - $read, PHP_BINARY_READ);
            if ($data === false) {
                $this->error("Failed to read data from socket");
            }
            $read += strlen($data);
            $parts[] = $data;
        }

        return implode($parts);
    }

    /**
     * {@inheritDoc}
     */
    public function getLine($length = null)
    {
        $data = @socket_read($this->socket, $length ?: 2056, PHP_NORMAL_READ);
        if ($data === false) {
            $this->error("Failed to read data line from socket");
        }
        // read CRLF
        @socket_read($this->socket, 32, PHP_NORMAL_READ);
        return rtrim($data);
    }
}
