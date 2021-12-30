<?php

declare(strict_types=1);


namespace Pheanstalk\Socket;

use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\SocketException;

/**
 * A Socket implementation using the standard file functions.
 */
abstract class FileSocket implements SocketInterface
{
    /** @var ?resource */
    protected $socket;

    /**
     * Writes data to the socket.
     */
    public function write(string $data): void
    {
        if (!isset($this->socket)) {
            $this->throw();
        }
        $retries = 0;
        error_clear_last();
        while ($data !== "" && $retries < 10) {
            $written = fwrite($this->socket, $data);

            if ($written === false) {
                $this->throwException();
            } elseif ($written === 0) {
                $retries++;
                continue;
            }
            $data = substr($data, $written);
        }

        if ($data !== "") {
            throw new SocketException('Write failed');
        }
    }

    private function throwException(): never
    {
        if (null === $error = error_get_last()) {
            throw new SocketException('Unknown error');
        }
        throw new SocketException($error['message'], $error['type']);
    }

    private function throw(): never
    {
        throw new SocketException('The connection was closed');
    }

    /**
     * Reads up to $length bytes from the socket.
     * @param int<0, max> $length
     */
    public function read(int $length): string
    {
        if (!isset($this->socket)) {
            $this->throw();
        }
        $buffer = '';
        while (0 < $remaining = $length - mb_strlen($buffer, '8bit')) {
            $result = fread($this->socket, $remaining);
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
        if (!isset($this->socket)) {
            $this->throw();
        }
        $result = fgets($this->socket, 8192);
        if ($result === false) {
            $this->throwException();
        }
        return rtrim($result);
    }

    /**
     * Disconnect the socket; subsequent usage of the socket will fail.
     */
    public function disconnect(): void
    {
        if (!isset($this->socket)) {
            $this->throw();
        }
        fclose($this->socket);
        $this->socket = null;
    }
}
