<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration;

use Pheanstalk\Connection;
use Pheanstalk\Exception\ConnectionException;
use Pheanstalk\PheanstalkManager;
use Pheanstalk\PheanstalkPublisher;
use Pheanstalk\PheanstalkSubscriber;
use Pheanstalk\Socket\SocketSocket;
use Pheanstalk\SocketFactory;
use Pheanstalk\Values\SocketImplementation;
use Pheanstalk\Values\Timeout;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SocketSocket::class)]
#[CoversClass(PheanstalkSubscriber::class)]
#[CoversClass(PheanstalkManager::class)]
#[CoversClass(PheanstalkPublisher::class)]
final class SocketPheanstalkTest extends PheanstalkTestBase
{
    use ConstructWithConnectionObjectTests;

    protected function getConnection(): Connection
    {
        return new Connection(new SocketFactory($this->getHost(), 11300, SocketImplementation::SOCKET, connectTimeout: new Timeout(1)));
    }


    public function testConnectTimeout(): void
    {
        // We use a non routable IP address to force a connection timeout
        $start = microtime(true);
        $factory = new SocketFactory("240.0.0.1", 11300, SocketImplementation::SOCKET, connectTimeout: new Timeout(1, 1));
        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('Connection failed or timed out');
        try {
            $factory->create();
        } catch (\Throwable $t) {
            self::assertGreaterThanOrEqual(1, microtime(true) - $start);
            throw $t;
        }
    }
}
