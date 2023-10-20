<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration;

use Pheanstalk\Command\UseCommand;
use Pheanstalk\Connection;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\TubeName;

abstract class ConnectionTestBase extends PheanstalkTestBase
{
    public function testDispatchCommandAfterDisconnect(): void
    {
        $this->expectNotToPerformAssertions();
        $connection = $this->getConnection();
        $connection->connect();
        $connection->disconnect();

        $connection->dispatchCommand(new UseCommand(new TubeName('tube5')));
    }

    protected function getPheanstalk(): Pheanstalk
    {
        return new Pheanstalk($this->getConnection());
    }

    abstract protected function getConnection(): Connection;
}