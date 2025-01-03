<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Socket;

use Pheanstalk\Socket\SocketSocket;
use Pheanstalk\Values\Timeout;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SocketSocket::class)]
final class SocketSocketTest extends TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function testSocketCreateErrors(): void
    {
        $mock = $this->getFunctionMock('Pheanstalk\\Socket', 'socket_create');
        $mock->expects($this->once())->willReturn(false);

        $mock = $this->getFunctionMock('Pheanstalk\\Socket', 'socket_strerror');
        $mock->expects($this->once())->willReturn('test message');
        $this->expectExceptionMessageMatches('/test message/');

        $socket = new SocketSocket(
            host: "333.333.333.333",
            port: 0,
            connectTimeout: new Timeout(1),
            receiveTimeout: new Timeout(1),
            sendTimeout: new Timeout(1)
        );
    }
}
