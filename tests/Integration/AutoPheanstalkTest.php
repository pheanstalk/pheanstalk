<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration;

use Pheanstalk\Command\KickCommand;
use Pheanstalk\Command\ListTubesCommand;
use Pheanstalk\Command\ListTubesWatchedCommand;
use Pheanstalk\Command\ListTubeUsedCommand;
use Pheanstalk\Command\PeekCommand;
use Pheanstalk\Command\ReserveCommand;
use Pheanstalk\Command\ReserveWithTimeoutCommand;
use Pheanstalk\Command\StatsCommand;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\Timeout;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Pheanstalk::class)]
#[CoversClass(ResponseType::class)]
#[CoversClass(StatsCommand::class)]
#[CoversClass(KickCommand::class)]
#[CoversClass(ListTubeUsedCommand::class)]
#[CoversClass(ListTubesCommand::class)]
#[CoversClass(ListTubesWatchedCommand::class)]
#[CoversClass(PeekCommand::class)]
#[CoversClass(ReserveCommand::class)]
#[CoversClass(ReserveWithTimeoutCommand::class)]
final class AutoPheanstalkTest extends PheanstalkTestBase
{
    protected function getPheanstalk(): Pheanstalk
    {
        return Pheanstalk::create($this->getHost(), connectTimeout: new Timeout(1));
    }
}
