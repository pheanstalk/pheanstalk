<?php


namespace Pheanstalk;

use Pheanstalk\Socket\FsockopenSocket;
use Pheanstalk\Socket\SocketSocket;
use Pheanstalk\Socket\StreamSocket;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class SocketFactoryTest extends TestCase
{
    public function testAutoDetect()
    {
        $socketFactory = new SocketFactory(SERVER_HOST, 11300, 10);
        self::assertSame(SocketFactory::SOCKET, $socketFactory->getImplementation());
    }

    public function testImplementations()
    {
        $socketFactory = new SocketFactory(SERVER_HOST, 11300, 10, SocketFactory::SOCKET);
        self::assertInstanceOf(SocketSocket::class, $socketFactory->create());

        $socketFactory = new SocketFactory(SERVER_HOST, 11300, 10, SocketFactory::STREAM);
        self::assertInstanceOf(StreamSocket::class, $socketFactory->create());

        $socketFactory = new SocketFactory(SERVER_HOST, 11300, 10, SocketFactory::FSOCKOPEN);
        self::assertInstanceOf(FsockopenSocket::class, $socketFactory->create());

        $socketFactory = new SocketFactory(SERVER_HOST, 11300, 10, SocketFactory::AUTODETECT);
        self::assertInstanceOf(SocketSocket::class, $socketFactory->create());
    }
}
