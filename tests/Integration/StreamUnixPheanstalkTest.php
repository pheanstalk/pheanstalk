<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration;

use Pheanstalk\Connection;
use Pheanstalk\PheanstalkManager;
use Pheanstalk\PheanstalkPublisher;
use Pheanstalk\PheanstalkSubscriber;
use Pheanstalk\Socket\FileSocket;
use Pheanstalk\Socket\StreamSocket;
use Pheanstalk\SocketFactory;
use Pheanstalk\Values\SocketImplementation;
use Pheanstalk\Values\Timeout;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(StreamSocket::class)]
#[CoversClass(FileSocket::class)]
#[CoversClass(PheanstalkSubscriber::class)]
#[CoversClass(PheanstalkManager::class)]
#[CoversClass(PheanstalkPublisher::class)]
final class StreamUnixPheanstalkTest extends PheanstalkTestBase
{
    use ConstructWithConnectionObjectTests;

    protected function getConnection(): Connection
    {
        return new Connection(new SocketFactory($this->getHost(), implementation: SocketImplementation::STREAM, connectTimeout: new Timeout(1)));
    }
}
