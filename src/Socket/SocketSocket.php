<?php

declare(strict_types=1);


namespace Pheanstalk\Socket;

use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\ConnectionException;
use Pheanstalk\Values\Timeout;
use Socket;

/**
 * A Socket implementation using the Sockets extension
 * @internal
 */
final class SocketSocket implements SocketInterface
{
    private null|\Socket $socket;

    public function __construct(
        string $host,
        int $port,
        Timeout $connectTimeout,
        Timeout $sendTimeout,
        Timeout $receiveTimeout
    ) {
        if (str_starts_with($host, 'unix://')) {
            $socket = @socket_create(AF_UNIX, SOCK_STREAM, SOL_SOCKET);
        } else {
            $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        }
        if ($socket === false) {
            throw new ConnectionException(0, "Failed to create socket");
        }


        socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, $connectTimeout->toArray());
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $connectTimeout->toArray());

        if (!str_starts_with($host, 'unix://')) {
            $addresses = gethostbynamel($host);
            if ($addresses === false) {
                throw new ConnectionException(0, "Could not resolve hostname $host");
            }
            if (@socket_connect($socket, $addresses[0], $port) === false) {
                $this->throwException($socket);
            }
        } elseif (@socket_connect($socket, substr($host, 7)) === false) {
            $this->throwException($socket);
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
                $this->throwException($socket);
            }
            $data = substr($data, $written);
        }
    }

    private function throwException(Socket $socket): never
    {
        $error = socket_last_error($socket);
        throw new ConnectionException($error, socket_strerror($error));
    }

    private function getSocket(): Socket
    {
        if (!isset($this->socket)) {
            throw new ConnectionException(0, 'The connection was closed');
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
                $this->throwException($socket);
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
                $this->throwException($socket);
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
