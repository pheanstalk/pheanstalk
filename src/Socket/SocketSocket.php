<?php

declare(strict_types=1);


namespace Pheanstalk\Socket;

use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\ConnectionException;
use Pheanstalk\Exception\SocketException;
use Pheanstalk\Values\Timeout;
use Socket;

/**
 * A Socket implementation using the Sockets extension
 */
class SocketSocket implements SocketInterface
{
    private null|\Socket $socket;

    public function __construct(
        string $host,
        int $port,
        Timeout $connectTimeout,
        Timeout $sendTimeout,
        Timeout $receiveTimeout
    ) {
        if (!extension_loaded('sockets')) {
            throw new \Exception('Sockets extension not found');
        }

        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            $this->throwException();
        }


        socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, $connectTimeout->toArray());
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $connectTimeout->toArray());
        if (socket_set_block($socket) === false) {
            throw new ConnectionException(0, "Failed to set socket to blocking mode");
        }

        $addresses = gethostbynamel($host);
        if ($addresses === false) {
            throw new ConnectionException(0, "Could not resolve hostname $host");
        }
        if (@socket_connect($socket, $addresses[0], $port) === false) {
            $error = socket_last_error($socket);
            throw new ConnectionException($error, socket_strerror($error));
        }

        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, $sendTimeout->toArray());
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $receiveTimeout->toArray());

        $this->socket = $socket;
    }

    /**
     * Writes data to the socket.
     */
    public function write(string $data): void
    {
        $socket = $this->getSocket();
        while ($data !== "") {
            $written = @socket_write($socket, $data);
            if ($written === false) {
                $this->throwException();
            }
            $data = substr($data, $written);
        }
    }

    private function throwException(): never
    {
        if (isset($this->socket)) {
            $error = socket_last_error($this->socket);
            throw new SocketException(socket_strerror($error), $error);
        }
        throw new SocketException("Unknown error");
    }

    private function getSocket(): Socket
    {
        if (!isset($this->socket)) {
            throw new SocketException('The connection was closed');
        }
        return $this->socket;
    }

    /**
     * Reads up to $length bytes from the socket.
     *
     * @return string
     */
    public function read(int $length): string
    {
        $socket = $this->getSocket();

        $buffer = '';
        while (mb_strlen($buffer, '8bit') < $length) {
            $result = @socket_read($socket, $length - mb_strlen($buffer, '8bit'));
            if ($result === false) {
                $this->throwException();
            }
            $buffer .= $result;
        }

        return $buffer;
    }

    public function getLine(): string
    {
        $socket = $this->getSocket();

        $buffer = '';
        // Reading stops at \r or \n. In case it stopped at \r we must continue reading.
        while (!str_ends_with($buffer, "\n")) {
            $result = @socket_read($socket, 1024, PHP_NORMAL_READ);
            if ($result === false) {
                $this->throwException();
            }
            $buffer .= $result;
        }



        return rtrim($buffer);
    }

    /**
     * Disconnect the socket; subsequent usage of the socket will fail.
     * This function is idempotent
     * @idempotent
     */
    public function disconnect(): void
    {
        if (isset($this->socket)) {
            socket_close($this->socket);
            unset($this->socket);
        }
    }
}
