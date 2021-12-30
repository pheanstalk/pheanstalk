<?php

declare(strict_types=1);


namespace Pheanstalk;

use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\NoImplementationException;
use Pheanstalk\Socket\FsockopenSocket;
use Pheanstalk\Socket\SocketSocket;
use Pheanstalk\Socket\StreamSocket;

class SocketFactory implements SocketFactoryInterface
{
    private readonly SocketImplementation $implementation;

    public function __construct(
        private readonly string $host,
        private readonly  int $port,
        private readonly int $timeout = 10,
        null|SocketImplementation $implementation = null
    ) {
        $this->implementation = $implementation ?? $this->detectImplementation();
    }

    private function detectImplementation(): SocketImplementation
    {
        // Prefer socket
        if (extension_loaded('sockets')) {
            return SocketImplementation::SOCKET;
        }

        // Then fall back to stream
        if (function_exists('stream_socket_client')) {
            return SocketImplementation::STREAM;
        }

        // Then fall back to fsockopen
        if (function_exists('fsockopen')) {
            return SocketImplementation::FSOCKOPEN;
        }
        throw new NoImplementationException();
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
        return match ($this->implementation) {
            SocketImplementation::SOCKET => $this->createSocketSocket(),
            SocketImplementation::STREAM => $this->createStreamSocket(),
            SocketImplementation::FSOCKOPEN => $this->createFsockopenSocket()
        };
    }
}
