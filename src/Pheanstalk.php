<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Command\ReserveJobCommand;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\PheanstalkManagerInterface;
use Pheanstalk\Contract\PheanstalkPublisherInterface;
use Pheanstalk\Contract\PheanstalkSubscriberInterface;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Values\Job;
use Pheanstalk\Values\JobState;
use Pheanstalk\Values\JobStats;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ServerStats;
use Pheanstalk\Values\Success;
use Pheanstalk\Values\TubeList;
use Pheanstalk\Values\TubeName;
use Pheanstalk\Values\TubeStats;

/**
 * Pheanstalk is a PHP client for the beanstalkd workqueue.
 */
class Pheanstalk implements PheanstalkManagerInterface, PheanstalkPublisherInterface, PheanstalkSubscriberInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Static constructor that uses auto-detection to choose an underlying socket implementation
     */
    public static function create(string $host, int $port = 11300, int $connectTimeout = 10): self
    {
        return self::createWithFactory(new SocketFactory($host, $port, $connectTimeout));
    }

    /**
     * Static constructor that uses a given socket factory for underlying connections
     */
    public static function createWithFactory(SocketFactoryInterface $factory): self
    {
        return new self(new Connection($factory));
    }

    // ----------------------------------------

    public function bury(JobIdInterface $job, int $priority = PheanstalkPublisherInterface::DEFAULT_PRIORITY): void
    {
        $command = new Command\BuryCommand($job, $priority);
        $command->interpret($this->dispatch2($command));
    }

    public function delete(JobIdInterface $job): void
    {
        $command = new Command\DeleteCommand($job);
        $command->interpret($this->dispatch2($command));
    }

    public function ignore(TubeName $tube): int
    {
        $command = new Command\IgnoreCommand($tube);
        return $command->interpret($this->dispatch2($command));
    }

    public function kick(int $max): int
    {
        $command = new Command\KickCommand($max);
        return $command->interpret($this->dispatch2($command));
    }

    public function kickJob(JobIdInterface $job): void
    {
        $command = new Command\KickJobCommand($job);
        $command->interpret($this->dispatch2($command));
    }

    private function dispatch2(CommandInterface $command): RawResponse
    {
        return $this->connection->dispatchCommand($command);
    }

    public function listTubes(): TubeList
    {
        $command = new Command\ListTubesCommand();
        return $command->interpret($this->dispatch2($command));
    }

    public function listTubesWatched(): TubeList
    {
        $command = new Command\ListTubesWatchedCommand();
        $response = $this->dispatch2($command);

        return $command->interpret($response);
    }

    public function listTubeUsed(): TubeName
    {
        $command = new Command\ListTubeUsedCommand();
        return $command->interpret($this->dispatch2($command));
    }

    public function pauseTube(TubeName $tube, int $delay): void
    {
        $command = new Command\PauseTubeCommand($tube, $delay);
        $command->interpret($this->dispatch2($command));
    }

    public function resumeTube(TubeName $tube): void
    {
        // Pause a tube with zero delay will resume the tube
        $this->pauseTube($tube, 0);
    }

    public function peek(JobIdInterface $job): Job
    {
        $command = new Command\PeekJobCommand($job);
        return $command->interpret($this->dispatch2($command));
    }

    public function peekReady(): null|Job
    {
        return $this->peekState(JobState::READY);
    }

    public function peekDelayed(): null|Job
    {
        return $this->peekState(JobState::DELAYED);
    }

    private function peekState(JobState $state): null|Job
    {
        $command = new Command\PeekCommand($state);
        $response = $command->interpret($this->dispatch2($command));
        return $response instanceof Success ? null : $response;
    }

    public function peekBuried(): null|Job
    {
        return $this->peekState(JobState::BURIED);
    }

    public function put(
        string $data,
        int $priority = PheanstalkPublisherInterface::DEFAULT_PRIORITY,
        int $delay = PheanstalkPublisherInterface::DEFAULT_DELAY,
        int $ttr = PheanstalkPublisherInterface::DEFAULT_TTR
    ): JobIdInterface {
        $command = new Command\PutCommand($data, $priority, $delay, $ttr);
        return $command->interpret($this->dispatch2($command));
    }

    public function release(
        JobIdInterface $job,
        int $priority = PheanstalkPublisherInterface::DEFAULT_PRIORITY,
        int $delay = PheanstalkPublisherInterface::DEFAULT_DELAY
    ): void {
        $command = new Command\ReleaseCommand($job, $priority, $delay);
        $command->interpret($this->dispatch2($command));
    }


    public function reserve(): Job
    {
        $command = new Command\ReserveCommand();

        return $command->interpret($this->dispatch2($command));
    }

    /**
     * @param int<0, max> $timeout
     * @throws Exception\UnsupportedResponseException|Exception\MalformedResponseException|Exception\DeadlineSoonException
     */
    public function reserveWithTimeout(int $timeout): null|Job
    {
        $command = new Command\ReserveWithTimeoutCommand($timeout);
        $response = $command->interpret($this->dispatch2($command));

        if ($response instanceof Success) {
            return null;
        }

        return $response;
    }

    public function statsJob(JobIdInterface $job): JobStats
    {
        $command = new Command\StatsJobCommand($job);
        return $command->interpret($this->dispatch2($command));
    }

    public function statsTube(TubeName $tube): TubeStats
    {
        $command = new Command\StatsTubeCommand($tube);
        return $command->interpret($this->dispatch2($command));
    }



    public function stats(): ServerStats
    {
        $command = new Command\StatsCommand();
        return $command->interpret($this->dispatch2($command));
    }

    public function touch(JobIdInterface $job): void
    {
        $command = new Command\TouchCommand($job);
        $command->interpret($this->dispatch2($command));
    }

    public function useTube(TubeName $tube): void
    {
        $command = new Command\UseCommand($tube);
        $response = $this->dispatch2($command);
        $command->interpret($response);
    }

    public function watch(TubeName $tube): int
    {
        $command = new Command\WatchCommand($tube);
        return $command->interpret($this->dispatch2($command));
    }

    public function reserveJob(JobIdInterface $job): Job
    {
        $command = new ReserveJobCommand($job);
        return $command->interpret($this->dispatch2($command));
    }
}
