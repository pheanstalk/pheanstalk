<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Command\BuryCommand;
use Pheanstalk\Command\ReserveJobCommand;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\PheanstalkPublisherInterface;
use Pheanstalk\Contract\PheanstalkSubscriberInterface;
use Pheanstalk\Values\Job;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\Success;
use Pheanstalk\Values\TubeList;
use Pheanstalk\Values\TubeName;

final class PheanstalkSubscriber implements PheanstalkSubscriberInterface
{
    use StaticFactoryTrait;

    private function dispatch(CommandInterface $command): RawResponse
    {
        return $this->connection->dispatchCommand($command);
    }

    public function delete(JobIdInterface $job): void
    {
        $command = new Command\DeleteCommand($job);
        $command->interpret($this->dispatch($command));
    }

    public function ignore(TubeName $tube): int
    {
        $command = new Command\IgnoreCommand($tube);
        return $command->interpret($this->dispatch($command));
    }


    public function listTubesWatched(): TubeList
    {
        $command = new Command\ListTubesWatchedCommand();
        $response = $this->dispatch($command);

        return $command->interpret($response);
    }

    public function release(
        JobIdInterface $job,
        int $priority = PheanstalkPublisherInterface::DEFAULT_PRIORITY,
        int $delay = PheanstalkPublisherInterface::DEFAULT_DELAY
    ): void {
        $command = new Command\ReleaseCommand($job, $priority, $delay);
        $command->interpret($this->dispatch($command));
    }


    public function reserve(): Job
    {
        $command = new Command\ReserveCommand();

        return $command->interpret($this->dispatch($command));
    }

    /**
     * @param int<0, max> $timeout
     * @throws Exception\UnsupportedResponseException|Exception\MalformedResponseException|Exception\DeadlineSoonException
     */
    public function reserveWithTimeout(int $timeout): null|Job
    {
        $command = new Command\ReserveWithTimeoutCommand($timeout);
        $response = $command->interpret($this->dispatch($command));

        if ($response instanceof Success) {
            return null;
        }

        return $response;
    }

    public function touch(JobIdInterface $job): void
    {
        $command = new Command\TouchCommand($job);
        $command->interpret($this->dispatch($command));
    }

    public function watch(TubeName $tube): int
    {
        $command = new Command\WatchCommand($tube);
        return $command->interpret($this->dispatch($command));
    }

    public function bury(JobIdInterface $job, int $priority = PheanstalkPublisherInterface::DEFAULT_PRIORITY): void
    {
        $command = new BuryCommand($job, $priority);
        $command->interpret($this->connection->dispatchCommand($command));
    }

    public function reserveJob(JobIdInterface $job): Job
    {
        $command = new ReserveJobCommand($job);
        return $command->interpret($this->dispatch($command));
    }
}
