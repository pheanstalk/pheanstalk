<?php

declare(strict_types=1);


namespace Pheanstalk;

use Pheanstalk\Socket\FsockopenSocket;
use Pheanstalk\Socket\SocketSocket;
use Pheanstalk\Socket\StreamSocket;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class SocketFactoryTest extends TestCase
{
    public function testImplementations()
    {
        $socketFactory = new SocketFactory(SERVER_HOST, 11300, 10, SocketImplementation::SOCKET);
        Assert::assertInstanceOf(SocketSocket::class, $socketFactory->create());

        $socketFactory = new SocketFactory(SERVER_HOST, 11300, 10, SocketImplementation::STREAM);
        Assert::assertInstanceOf(StreamSocket::class, $socketFactory->create());

        $socketFactory = new SocketFactory(SERVER_HOST, 11300, 10, SocketImplementation::FSOCKOPEN);
        Assert::assertInstanceOf(FsockopenSocket::class, $socketFactory->create());

        $socketFactory = new SocketFactory(SERVER_HOST, 11300, 10);
        Assert::assertInstanceOf(SocketSocket::class, $socketFactory->create());
    }
}
