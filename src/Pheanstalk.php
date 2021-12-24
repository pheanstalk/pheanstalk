<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Command\ReserveJobCommand;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Contract\PheanstalkManagerInterface;
use Pheanstalk\Contract\PheanstalkPublisherInterface;
use Pheanstalk\Contract\PheanstalkSubscriberInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\Response\EmptySuccessResponse;
use Pheanstalk\Response\JobResponse;

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

    /**
     * {@inheritdoc}
     */
    public function bury(JobIdInterface $job, int $priority = PheanstalkPublisherInterface::DEFAULT_PRIORITY): void
    {
        $this->dispatch(new Command\BuryCommand($job, $priority));
    }

    public function delete(JobIdInterface $job): void
    {
        $this->dispatch(new Command\DeleteCommand($job));
    }

    public function ignore(string $tube): void
    {
        $this->dispatch(new Command\IgnoreCommand(new TubeName($tube)));
    }

    /**
     * {@inheritdoc}
     */
    public function kick(int $max): int
    {
        $response = $this->dispatch(new Command\KickCommand($max));

        return $response['kicked'];
    }

    /**
     * {@inheritdoc}
     */
    public function kickJob(JobIdInterface $job): void
    {
        $this->dispatch(new Command\KickJobCommand($job));
    }

    /**
     * {@inheritdoc}
     */
    public function listTubes(): array
    {
        return (array)$this->dispatch(
            new Command\ListTubesCommand()
        );
    }

    public function listTubesWatched(): array
    {
        /** @var ArrayResponse $response */
        $response = $this->dispatch(new Command\ListTubesWatchedCommand());
        return (array) $response;

    }

    public function listTubeUsed(): string
    {

        $response = $this->dispatch(
            new Command\ListTubeUsedCommand()
        );
        return $response['tube'];
    }

    /**
     * {@inheritdoc}
     */
    public function pauseTube(string $tube, int $delay): void
    {
        $this->dispatch(new Command\PauseTubeCommand(new TubeName($tube), $delay));
    }

    /**
     * {@inheritdoc}
     */
    public function resumeTube(string $tube): void
    {
        // Pause a tube with zero delay will resume the tube
        $this->pauseTube($tube, 0);
    }

    public function peek(JobIdInterface $job): Job
    {
        /** @var JobResponse $response */
        $response = $this->dispatch(
            new Command\PeekJobCommand($job)
        );

        return new Job($response->getId(), $response->getData());
    }

    public function peekReady(): ?Job
    {
        try {
            /** @var JobResponse $response */
            $response = $this->dispatch(
                new Command\PeekCommand(CommandType::PEEK_READY)
            );

            return new Job($response->getId()->getId(), $response->getData());
        } catch (JobNotFoundException $e) {
            return null;
        }
    }

    public function peekDelayed(): ?Job
    {
        try {
            $response = $this->dispatch(
                new Command\PeekCommand(CommandType::PEEK_DELAYED)
            );

            return new Job($response->getId()->getId(), $response->getData());
        } catch (JobNotFoundException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function peekBuried(): ?Job
    {
        try {
            $response = $this->dispatch(
                new Command\PeekCommand(CommandType::PEEK_BURIED)
            );

            return new Job($response->getId()->getId(), $response->getData());
        } catch (JobNotFoundException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function put(
        string $data,
        int $priority = PheanstalkPublisherInterface::DEFAULT_PRIORITY,
        int $delay = PheanstalkPublisherInterface::DEFAULT_DELAY,
        int $ttr = PheanstalkPublisherInterface::DEFAULT_TTR
    ): Job {
        /** @var JobResponse $response */
        $response = $this->dispatch(
            new Command\PutCommand($data, $priority, $delay, $ttr)
        );

        return new Job($response->getId(), $data);
    }

    /**
     * {@inheritdoc}
     */
    public function release(
        JobIdInterface $job,
        int $priority = PheanstalkPublisherInterface::DEFAULT_PRIORITY,
        int $delay = PheanstalkPublisherInterface::DEFAULT_DELAY
    ): void {
        $this->dispatch(
            new Command\ReleaseCommand($job, $priority, $delay)
        );
    }


    public function reserve(): Job
    {
        /** @var JobResponse $response */
        $response = $this->dispatch(
            new Command\ReserveCommand()
        );

        return new Job($response->getId(), $response->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function reserveWithTimeout(int $timeout): ?Job
    {
        /** @var JobResponse|EmptySuccessResponse $response */
        $response = $this->dispatch(
            new Command\ReserveWithTimeoutCommand($timeout)
        );

        if ($response instanceof EmptySuccessResponse) {
            return null;
        }

        return new Job($response->getId(), $response->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function statsJob(JobIdInterface $job): ResponseInterface
    {
        return $this->dispatch(new Command\StatsJobCommand($job));
    }

    /**
     * {@inheritdoc}
     */
    public function statsTube(string $tube): ResponseInterface
    {
        return $this->dispatch(new Command\StatsTubeCommand(new TubeName($tube)));
    }

    public function stats(): ArrayResponse
    {
        return $this->dispatch(new Command\StatsCommand());
    }

    /**
     * {@inheritdoc}
     */
    public function touch(JobIdInterface $job): void
    {
        $this->dispatch(new Command\TouchCommand($job));
    }

    public function useTube(string $tube): void
    {
        $this->dispatch(new Command\UseCommand(new TubeName($tube)));
    }

    public function watch(string $tube): void
    {
        $this->dispatch(new Command\WatchCommand(new TubeName($tube)));
    }


    // ----------------------------------------

    /**
     * Dispatches the specified command to the connection object.
     */
    private function dispatch(CommandInterface $command): ResponseInterface
    {
        return $this->connection->dispatchCommand($command);

    }

    /**
     * Creates a new connection object, based on the existing connection object,
     * and re-establishes the used tube and watchlist.
     */
    private function reconnect()
    {
        throw new \RuntimeException('not supported');
        $this->connection->disconnect();

        if ($this->using !== PheanstalkInterface::DEFAULT_TUBE) {
            $this->dispatch(new Command\UseCommand($this->using));
        }

        foreach ($this->watching as $tube => $true) {
            if ($tube !== PheanstalkInterface::DEFAULT_TUBE) {
                unset($this->watching[$tube]);
                $this->watch($tube);
            }
        }

        if (!isset($this->watching[PheanstalkInterface::DEFAULT_TUBE])) {
            $this->ignore(PheanstalkInterface::DEFAULT_TUBE);
        }
    }

    public function reserveJob(JobIdInterface $job): Job
    {
        /** @var JobResponse $response */
        $response = $this->dispatch(new ReserveJobCommand($job));
        return new Job($response->getId(), $response->getData());
    }
}
