<?php

declare(strict_types=1);


namespace Pheanstalk\Socket;

use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\ConnectionException;
use Pheanstalk\Values\Timeout;

/**
 * A Socket implementation using the standard file functions.
 * @internal
 */
abstract class FileSocket implements SocketInterface
{
    /**
     * @phpstan-var resource
     * @psalm-var resource|closed-resource
     */
    private $socket;

    protected function __construct(mixed $socket, Timeout $receiveTimeout)
    {
        if (!is_resource($socket)) {
            throw new \InvalidArgumentException("A valid resource is required");
        }

        stream_set_timeout($socket, $receiveTimeout->seconds, $receiveTimeout->microSeconds);
        $this->socket = $socket;
    }

    /**
     * @return resource
     */
    final protected function getSocket()
    {
        if (!is_resource($this->socket)) {
            throw new ConnectionException(0, "The connection was closed");
        }
        return $this->socket;
    }

    /**
     * Writes data to the socket.
     */
    public function write(string $data): void
    {
        $socket = $this->getSocket();

        $retries = 0;
        error_clear_last();
        while ($data !== "" && $retries < 10) {
            $written = fwrite($socket, $data);

            if ($written === false) {
                $this->throwException();
            } elseif ($written === 0) {
                $retries++;
                continue;
            }
            $data = substr($data, $written);
        }

        if ($data !== "") {
            throw new ConnectionException(0, 'Write failed for 10 attempts');
        }
    }

    private function throwException(): never
    {
        if (null === $error = error_get_last()) {
            throw new ConnectionException(0, 'Unknown error');
        }
        throw new ConnectionException($error['type'], $error['message']);
    }

    /**
     * Reads up to $length bytes from the socket.
     * @param int<0, max> $length
     */
    public function read(int $length): string
    {
        $socket = $this->getSocket();
        $buffer = '';
        while (0 < $remaining = $length - mb_strlen($buffer, '8bit')) {
            $result = fread($socket, $remaining);
            if ($result === false) {
                $this->throwException();
            }
            $buffer .= $result;
        }
        return $buffer;
    }

    /**
     * Reads up to the next new-line.
     * Trailing whitespace is trimmed.
     */
    public function getLine(): string
    {
        $socket = $this->getSocket();
        $result = fgets($socket, 8192);
        if ($result === false) {
            $this->throwException();
        }
        return rtrim($result);
    }

    /**
     * Disconnect the socket; subsequent usage of the socket will fail.
     * @idempotent
     */
    public function disconnect(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
    }
}
