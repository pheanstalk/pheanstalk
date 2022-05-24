<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration;

use Pheanstalk\Connection;
use Pheanstalk\Pheanstalk;
use Pheanstalk\SocketFactory;
use Pheanstalk\Values\SocketImplementation;

/**
 * @covers \Pheanstalk\Socket\SocketSocket
 */
class SocketPheanstalkTest extends PheanstalkTest
{
    protected function getPheanstalk(string $host = SERVER_HOST): Pheanstalk
    {
        return new Pheanstalk(new Connection(new SocketFactory($host, 11300, SocketImplementation::SOCKET)));
    }
}
