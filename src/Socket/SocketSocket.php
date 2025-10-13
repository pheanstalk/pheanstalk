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
        private Timeout $receiveTimeout
    ) {
        if (str_starts_with($host, 'unix://')) {
            $socket = @socket_create(AF_UNIX, SOCK_STREAM, SOL_SOCKET);
        } else {
            $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        }
        if ($socket === false) {
            $errorCode = socket_last_error();
            $errorMessage = socket_strerror($errorCode);
            throw new ConnectionException($errorCode, "Failed to create socket: $errorMessage");
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
                $this->throwException($socket, "Connection failed or timed out");
            }
        } elseif (@socket_connect($socket, substr($host, 7)) === false) {
            $this->throwException($socket);
        }

        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, $sendTimeout->toArray());
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $this->receiveTimeout->toArray());

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
                if ($this->isInterruptedSystemCall($socket)) {
                    continue;
                }
                $this->throwException($socket);
            }
            $data = substr($data, $written);
        }
    }

    private function throwException(Socket $socket, string|null $errorPrefix = null): never
    {
        $errorCode = socket_last_error($socket);
        $error = socket_strerror($errorCode);
        throw new ConnectionException($errorCode, isset($errorPrefix) ? "$errorPrefix: $error" : $error);
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
     */
    public function read(int $length): string
    {
        $socket = $this->getSocket();

        $buffer = '';
        while (mb_strlen($buffer, '8bit') < $length) {
            $result = @socket_read($socket, $length - mb_strlen($buffer, '8bit'));
            if ($result === false || $result === '') {
                if ($this->isInterruptedSystemCall($socket)) {
                    continue;
                }
                $this->throwException($socket);
            }
            $buffer .= $result;
        }

        return $buffer;
    }

    public function getLine(?Timeout $readTimeout = null): string
    {
        $socket = $this->getSocket();
        socket_set_option(
            $socket,
            SOL_SOCKET,
            SO_RCVTIMEO,
            $this->receiveTimeout->add($readTimeout)->toArray()
        );

        $buffer = '';
        do {
            $byteRead = @socket_read($socket, 1);
            if ($byteRead === false || $byteRead === '') {
                if ($this->isInterruptedSystemCall($socket)) {
                    continue;
                }
                $this->throwException($socket);
            }
            $buffer .= $byteRead;
        } while ($byteRead !== "\n");

        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $this->receiveTimeout->toArray());

        return rtrim($buffer);
    }

    private function isInterruptedSystemCall(Socket $socket): bool
    {
        return \SOCKET_EINTR === socket_last_error($socket);
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
            $this->socket = null;
        }
    }
}
