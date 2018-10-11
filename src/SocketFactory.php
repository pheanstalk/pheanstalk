<?php


namespace Pheanstalk;

use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Socket\FsockopenSocket;
use Pheanstalk\Socket\SocketSocket;
use Pheanstalk\Socket\StreamSocket;

class SocketFactory implements SocketFactoryInterface
{
    public const AUTODETECT = 0;
    public const STREAM = 1;
    public const SOCKET = 2;
    public const FSOCKOPEN = 3;

    private $timeout;
    private $host;
    private $port;
    /** @var int */
    private $implementation;

    public function __construct(string $host, int $port, int $timeout = 10, $implementation = self::AUTODETECT)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->setImplementation($implementation);
    }

    public function getImplementation(): int
    {
        return $this->implementation;
    }

    public function setImplementation(int $implementation)
    {
        if ($implementation === self::AUTODETECT) {
            // Prefer socket
            if (extension_loaded('sockets')) {
                $this->implementation = self::SOCKET;
                return;
            }

            // Then fall back to stream
            if (function_exists('stream_socket_client')) {
                $this->implementation = self::STREAM;
                return;
            }

            // Then fall back to fsockopen
            if (function_exists('fsockopen')) {
                $this->implementation = self::FSOCKOPEN;
            }
        } else {
            $this->implementation = $implementation;
        }
    }

    private function createStreamSocket(): StreamSocket
    {
        return new StreamSocket($this->host, $this->port, $this->timeout);
    }

    private function createSocketSocket(): SocketSocket
    {
        return new SocketSocket($this->host, $this->port, $this->timeout);
    }

    private function createFsockopenSocket(): FsockopenSocket
    {
        return new FsockopenSocket($this->host, $this->port, $this->timeout);
    }

    /**
     * This function must return a connected socket that is ready for reading / writing.
     * @return SocketInterface
     */
    public function create(): SocketInterface
    {
        switch ($this->implementation) {
            case self::SOCKET:
                return $this->createSocketSocket();
            case self::STREAM:
                return $this->createStreamSocket();
            case self::FSOCKOPEN:
                return $this->createFsockopenSocket();
            default:
                throw new \RuntimeException("Unknown implementation");
        }
    }
}
