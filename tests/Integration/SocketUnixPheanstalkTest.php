<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration;

use Pheanstalk\Connection;
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
final class SocketUnixPheanstalkTest extends PheanstalkTestBase
{
    use ConstructWithConnectionObjectTests;

    protected function getConnection(): Connection
    {
        return new Connection(new SocketFactory($this->getHost(), implementation: SocketImplementation::SOCKET, connectTimeout: new Timeout(1)));
    }
}
