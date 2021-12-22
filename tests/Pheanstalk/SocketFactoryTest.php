<?php

declare(strict_types=1);


namespace Pheanstalk;

use Pheanstalk\Socket\FsockopenSocket;
use Pheanstalk\Socket\SocketSocket;
use Pheanstalk\Socket\StreamSocket;
use PHPUnit\Framework\TestCase;

class SocketFactoryTest extends TestCase
{
    public function testAutoDetect()
    {
        $socketFactory = new SocketFactory(SERVER_HOST, 11300, 10);
        $this->assertEquals(SocketFactory::SOCKET, $socketFactory->getImplementation());
    }

    public function testImplementations()
    {
        $socketFactory = new SocketFactory(SERVER_HOST, 11300, 10, SocketFactory::SOCKET);
        $this->assertInstanceOf(SocketSocket::class, $socketFactory->create());

        $socketFactory = new SocketFactory(SERVER_HOST, 11300, 10, SocketFactory::STREAM);
        $this->assertInstanceOf(StreamSocket::class, $socketFactory->create());

        $socketFactory = new SocketFactory(SERVER_HOST, 11300, 10, SocketFactory::FSOCKOPEN);
        $this->assertInstanceOf(FsockopenSocket::class, $socketFactory->create());

        $socketFactory = new SocketFactory(SERVER_HOST, 11300, 10, SocketFactory::AUTODETECT);
        $this->assertInstanceOf(SocketSocket::class, $socketFactory->create());
    }
}
