<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration;

use Pheanstalk\Connection;
use Pheanstalk\SocketFactory;
use Pheanstalk\Values\SocketImplementation;
use Pheanstalk\Values\Timeout;

/**
 * @covers \Pheanstalk\Socket\StreamSocket
 * @covers \Pheanstalk\Socket\FileSocket
 * @covers \Pheanstalk\PheanstalkSubscriber
 * @covers \Pheanstalk\PheanstalkManager
 * @covers \Pheanstalk\PheanstalkPublisher
 */
final class StreamUnixPheanstalkTest extends PheanstalkTestBase
{
    use ConstructWithConnectionObjectTests;

    protected function getConnection(): Connection
    {
        return new Connection(new SocketFactory($this->getHost(), implementation: SocketImplementation::STREAM, connectTimeout: new Timeout(1)));
    }
}
