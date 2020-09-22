<?php


namespace Pheanstalk\Socket;

use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\ConnectionException;
use Pheanstalk\Exception\SocketException;

/**
 * A Socket implementation using the Sockets extension
 */
class SocketSocket implements SocketInterface
{
    /** @var resource */
    private $socket;

    public function __construct(
        string $host,
        int $port,
        int $connectTimeout
    ) {
        if (!extension_loaded('sockets')) {
            throw new \Exception('Sockets extension not found');
        }

        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false) {
            $this->throwException();
        }

        $timeout = [
            'sec' => $connectTimeout,
            'usec' => 0
        ];

        $sendTimeout = socket_get_option($this->socket, SOL_SOCKET, SO_SNDTIMEO);
        $receiveTimeout = socket_get_option($this->socket, SOL_SOCKET, SO_RCVTIMEO);
        socket_set_option($this->socket, SOL_SOCKET, SO_KEEPALIVE, 1);
        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, $timeout);
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, $timeout);
        if (socket_set_block($this->socket) === false) {
            throw new ConnectionException(0, "Failed to set socket to blocking mode");
        }

        $addresses = gethostbynamel($host);
        if ($addresses === false) {
            throw new ConnectionException(0, "Could not resolve hostname $host");
        }
        if (@socket_connect($this->socket, $addresses[0], $port) === false) {
            $error = socket_last_error($this->socket);
            throw new ConnectionException($error, socket_strerror($error));
        };

        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, $sendTimeout);
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, $receiveTimeout);
    }

    /**
     * Writes data to the socket.
     *
     * @param string $data
     *
     * @return void
     */
    public function write(string $data): void
    {
        $this->checkClosed();
        while (!empty($data)) {
            $written = socket_write($this->socket, $data);
            if ($written === false) {
                $this->throwException();
            }
            $data = substr($data, $written);
        }
    }

    private function throwException()
    {
        $error = socket_last_error($this->socket);
        throw new SocketException(socket_strerror($error), $error);
    }

    private function checkClosed()
    {
        if (!isset($this->socket)) {
            throw new SocketException('The connection was closed');
        }
    }

    /**
     * Reads up to $length bytes from the socket.
     *
     * @return string
     */
    public function read(int $length): string
    {
        $this->checkClosed();

        $buffer = '';
        while (mb_strlen($buffer, '8BIT') < $length) {
            $result = socket_read($this->socket, $length - mb_strlen($buffer, '8BIT'));
            if ($result === false) {
                $this->throwException();
            }
            $buffer .= $result;
        }

        return $buffer;
    }

    public function getLine(): string
    {
        $this->checkClosed();

        $buffer = '';
        // Reading stops at \r or \n. In case it stopped at \r we must continue reading.
        while (substr($buffer, -1, 1) !== "\n") {
            $result = socket_read($this->socket, 1024, PHP_NORMAL_READ);
            if ($result === false) {
                $this->throwException();
            }
            $buffer .= $result;
        }



        return rtrim($buffer);
    }

    /**
     * Disconnect the socket; subsequent usage of the socket will fail.
     */
    public function disconnect(): void
    {
        $this->checkClosed();
        socket_close($this->socket);
        unset($this->socket);
    }
}
