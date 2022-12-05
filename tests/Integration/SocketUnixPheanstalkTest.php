<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration;

use Pheanstalk\Connection;
use Pheanstalk\Pheanstalk;
use Pheanstalk\SocketFactory;
use Pheanstalk\Values\SocketImplementation;
use Pheanstalk\Values\Timeout;

/**
 * @covers \Pheanstalk\Socket\SocketSocket
 * @covers \Pheanstalk\PheanstalkSubscriber
 * @covers \Pheanstalk\PheanstalkManager
 * @covers \Pheanstalk\PheanstalkPublisher
 */
class SocketUnixPheanstalkTest extends PheanstalkTest
{
    protected function getPheanstalk(): Pheanstalk
    {
        return new Pheanstalk(new Connection(new SocketFactory($this->getHost(), implementation: SocketImplementation::SOCKET, connectTimeout: new Timeout(1))));
    }
}
