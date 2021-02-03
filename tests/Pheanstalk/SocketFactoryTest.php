<?php

declare(strict_types=1);

namespace Pheanstalk\Tests;

use Pheanstalk\Socket\FsockopenSocket;
use Pheanstalk\Socket\SocketSocket;
use Pheanstalk\Socket\StreamSocket;
use Pheanstalk\SocketFactory;

class SocketFactoryTest extends BaseTestCase
{
    public function testAutoDetect()
    {
        $socketFactory = new SocketFactory(SERVER_HOST, 11300, 10);
        $this->assertSame(SocketFactory::SOCKET, $socketFactory->getImplementation());
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
