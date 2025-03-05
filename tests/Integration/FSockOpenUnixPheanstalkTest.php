<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration;

use Pheanstalk\Connection;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkManager;
use Pheanstalk\PheanstalkPublisher;
use Pheanstalk\PheanstalkSubscriber;
use Pheanstalk\Socket\FileSocket;
use Pheanstalk\Socket\FsockopenSocket;
use Pheanstalk\SocketFactory;
use Pheanstalk\Values\SocketImplementation;
use Pheanstalk\Values\Timeout;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FsockopenSocket::class)]
#[CoversClass(FileSocket::class)]
#[CoversClass(PheanstalkSubscriber::class)]
#[CoversClass(PheanstalkManager::class)]
#[CoversClass(PheanstalkPublisher::class)]
final class FSockOpenUnixPheanstalkTest extends PheanstalkTestBase
{
    protected function getPheanstalk(): Pheanstalk
    {
        return new Pheanstalk(new Connection(new SocketFactory($this->getHost(), implementation: SocketImplementation::FSOCKOPEN, connectTimeout: new Timeout(1, 0))));
    }
}
