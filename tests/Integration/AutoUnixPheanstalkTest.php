<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration;

use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\Timeout;

/**
 * @covers \Pheanstalk\Pheanstalk
 * @covers \Pheanstalk\Values\ResponseType
 * @covers \Pheanstalk\Command\StatsCommand
 * @covers \Pheanstalk\Command\KickCommand
 * @covers \Pheanstalk\Command\ListTubeUsedCommand
 * @covers \Pheanstalk\Command\ListTubesCommand
 * @covers \Pheanstalk\Command\ListTubesWatchedCommand
 * @covers \Pheanstalk\Command\PeekCommand
 * @covers \Pheanstalk\Command\ReserveCommand
 * @covers \Pheanstalk\Command\ReserveWithTimeoutCommand
 */
final class AutoUnixPheanstalkTest extends PheanstalkTestBase
{
    protected function getPheanstalk(): Pheanstalk
    {
        return Pheanstalk::create($this->getHost(), connectTimeout: new Timeout(1));
    }
}
