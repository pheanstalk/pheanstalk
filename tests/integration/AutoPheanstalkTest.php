<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration;

use Pheanstalk\Pheanstalk;

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
class AutoPheanstalkTest extends PheanstalkTest
{
    protected function getPheanstalk(string $host = SERVER_HOST): Pheanstalk
    {
        return Pheanstalk::create($host);
    }
}
