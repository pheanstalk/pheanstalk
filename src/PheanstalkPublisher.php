<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Command\ListTubeUsedCommand;
use Pheanstalk\Command\PutCommand;
use Pheanstalk\Command\UseCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\PheanstalkPublisherInterface;
use Pheanstalk\Values\TubeName;

/**
 * Implements the methods in the dispatcher interface.
 */
final class PheanstalkPublisher implements PheanstalkPublisherInterface
{
    use StaticFactoryTrait;

    public function listTubeUsed(): TubeName
    {
        $command = new ListTubeUsedCommand();
        return $command->interpret($this->connection->dispatchCommand($command));
    }

    public function useTube(TubeName $tube): void
    {
        $command = new UseCommand($tube);
        $command->interpret($this->connection->dispatchCommand($command));
    }


    public function put(
        string $data,
        int $priority = PheanstalkPublisherInterface::DEFAULT_PRIORITY,
        int $delay = PheanstalkPublisherInterface::DEFAULT_DELAY,
        int $timeToRelease = PheanstalkPublisherInterface::DEFAULT_TTR
    ): JobIdInterface {
        $command = new PutCommand($data, $priority, $delay, $timeToRelease);
        return $command->interpret($this->connection->dispatchCommand($command));
    }
}
