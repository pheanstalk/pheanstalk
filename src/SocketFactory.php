<?php

declare(strict_types=1);


namespace Pheanstalk;

use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\NoImplementationException;
use Pheanstalk\Socket\FsockopenSocket;
use Pheanstalk\Socket\SocketSocket;
use Pheanstalk\Socket\StreamSocket;
use Pheanstalk\Values\SocketImplementation;
use Pheanstalk\Values\Timeout;

final class SocketFactory implements SocketFactoryInterface
{
    private const DEFAULT_TIMEOUT = 10;
    public readonly SocketImplementation $implementation;

    private readonly Timeout $connectTimeout;
    private readonly Timeout $receiveTimeout;
    private readonly Timeout $sendTimeout;

    /**
     * @param string $host
     * @param int $port
     * @param SocketImplementation|null $implementation
     * @param Timeout|null $connectTimeout
     * @param Timeout|null $receiveTimeout
     * @param Timeout|null $sendTimeout the timeout used for sending data, not supported by all implementations
     * @throws NoImplementationException
     */
    public function __construct(
        private readonly string $host,
        private readonly int $port = self::DEFAULT_PORT,
        null|SocketImplementation $implementation = null,
        Timeout $connectTimeout = null,
        Timeout $receiveTimeout = null,
        Timeout $sendTimeout = null,
    ) {
        $this->implementation = $implementation ?? $this->detectImplementation();

        $this->connectTimeout = $connectTimeout ?? new Timeout(self::DEFAULT_TIMEOUT, 0);
        $this->receiveTimeout = $receiveTimeout ?? new Timeout(self::DEFAULT_TIMEOUT, 0);
        $this->sendTimeout = $sendTimeout ?? new Timeout(self::DEFAULT_TIMEOUT, 0);
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
        return new StreamSocket($this->host, $this->port, $this->connectTimeout, $this->receiveTimeout);
    }

    private function createSocketSocket(): SocketSocket
    {
        return new SocketSocket($this->host, $this->port, $this->connectTimeout, $this->sendTimeout, $this->receiveTimeout);
    }

    private function createFsockopenSocket(): FsockopenSocket
    {
        return new FsockopenSocket($this->host, $this->port, $this->connectTimeout, $this->receiveTimeout);
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
