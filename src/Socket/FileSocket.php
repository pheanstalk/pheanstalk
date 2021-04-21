<?php


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
     *
     * @param string $data
     *
     * @return void
     */
    public function write(string $data): void
    {
        $this->checkClosed();
        $retries = 0;
        error_clear_last();
        while (!empty($data) && $retries < 10) {
            $written = fwrite($this->socket, $data);

            if ($written === false) {
                $this->throwException();
            } elseif ($written === 0) {
                $retries++;
                continue;
            }
            $data = substr($data, $written);
        }

        if (!empty($data)) {
            throw new SocketException('Write failed');
        }
    }

    private function throwException()
    {
        if (null === $error = error_get_last()) {
            throw new SocketException('Unknown error');
        }
        throw new SocketException($error['message'], $error['type']);
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
        while (mb_strlen($buffer, '8bit') < $length) {
            $result = fread($this->socket, $length - mb_strlen($buffer, '8bit'));
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
     *
     * @param int
     */
    public function getLine(): string
    {
        $this->checkClosed();
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
        $this->checkClosed();
        fclose($this->socket);
        $this->socket = null;
    }
}
