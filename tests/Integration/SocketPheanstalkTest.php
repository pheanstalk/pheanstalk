<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration;

use Pheanstalk\Connection;
use Pheanstalk\SocketFactory;
use Pheanstalk\Values\SocketImplementation;
use Pheanstalk\Values\Timeout;

/**
 * @covers \Pheanstalk\Socket\SocketSocket
 * @covers \Pheanstalk\PheanstalkSubscriber
 * @covers \Pheanstalk\PheanstalkManager
 * @covers \Pheanstalk\PheanstalkPublisher
 */
final class SocketPheanstalkTest extends PheanstalkTestBase
{
    use ConstructWithConnectionObjectTests;

    protected function getConnection(): Connection
    {
        return new Connection(new SocketFactory($this->getHost(), 11300, SocketImplementation::SOCKET, connectTimeout: new Timeout(1)));
    }
}
