<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\PheanstalkManagerInterface;
use Pheanstalk\Contract\PheanstalkPublisherInterface;
use Pheanstalk\Contract\PheanstalkSubscriberInterface;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Values\Job;
use Pheanstalk\Values\JobStats;
use Pheanstalk\Values\ServerStats;
use Pheanstalk\Values\Timeout;
use Pheanstalk\Values\TubeList;
use Pheanstalk\Values\TubeName;
use Pheanstalk\Values\TubeStats;

/**
 * Pheanstalk is a PHP client for the beanstalkd work queue.
 * This class implements all functionality in one big object.
 * It is recommended to instead inject instances of the more specific interface implementations.
 * For example, your frontend is unlikely to subscribe to requests so probably does not need `PheanstalkPublisherInterface`
 * or `PheanstalkManagerInterface`.
 */
final class Pheanstalk implements PheanstalkManagerInterface, PheanstalkPublisherInterface, PheanstalkSubscriberInterface
{
    private PheanstalkManagerInterface $manager;
    private PheanstalkPublisherInterface $publisher;
    private PheanstalkSubscriberInterface $subscriber;

    public function __construct(private readonly Connection $connection)
    {
        $this->manager = new PheanstalkManager($this->connection);
        $this->subscriber = new PheanstalkSubscriber($this->connection);
        $this->publisher = new PheanstalkPublisher($this->connection);
    }

    /**
     * Static constructor that uses auto-detection to choose an underlying socket implementation
     */
    public static function create(
        string $host,
        int $port = 11300,
        Timeout $connectTimeout = null,
        Timeout $receiveTimeout = null
    ): static {
        return static::createWithFactory(new SocketFactory($host, $port, null, $connectTimeout, $receiveTimeout));
    }

    /**
     * Static constructor that uses a given socket factory for underlying connections
     */
    public static function createWithFactory(SocketFactoryInterface $factory): static
    {
        return new static(new Connection($factory));
    }


    public function kick(int $max): int
    {
        return $this->manager->kick($max);
    }

    public function kickJob(JobIdInterface $job): void
    {
        $this->manager->kickJob($job);
    }

    public function listTubes(): TubeList
    {
        return $this->manager->listTubes();
    }

    public function pauseTube(TubeName $tube, int $delay): void
    {
        $this->manager->pauseTube($tube, $delay);
    }

    public function resumeTube(TubeName $tube): void
    {
        $this->manager->resumeTube($tube);
    }

    public function peek(JobIdInterface $job): Job
    {
        return $this->manager->peek($job);
    }

    public function peekReady(): ?Job
    {
        return $this->manager->peekReady();
    }

    public function peekDelayed(): ?Job
    {
        return $this->manager->peekDelayed();
    }

    public function peekBuried(): ?Job
    {
        return $this->manager->peekBuried();
    }

    public function statsJob(JobIdInterface $job): JobStats
    {
        return $this->manager->statsJob($job);
    }

    public function statsTube(TubeName $tube): TubeStats
    {
        return $this->manager->statsTube($tube);
    }

    public function stats(): ServerStats
    {
        return $this->manager->stats();
    }

    public function bury(JobIdInterface $job, int $priority = self::DEFAULT_PRIORITY): void
    {
        $this->subscriber->bury($job, $priority);
    }

    public function listTubeUsed(): TubeName
    {
        return $this->publisher->listTubeUsed();
    }

    public function put(
        string $data,
        int $priority = self::DEFAULT_PRIORITY,
        int $delay = self::DEFAULT_DELAY,
        int $timeToRelease = self::DEFAULT_TTR
    ): JobIdInterface {
        return $this->publisher->put($data, $priority, $delay, $timeToRelease);
    }

    public function useTube(TubeName $tube): void
    {
        $this->publisher->useTube($tube);
    }

    public function delete(JobIdInterface $job): void
    {
        $this->subscriber->delete($job);
    }

    public function ignore(TubeName $tube): int
    {
        return $this->subscriber->ignore($tube);
    }

    public function listTubesWatched(): TubeList
    {
        return $this->subscriber->listTubesWatched();
    }

    public function release(
        JobIdInterface $job,
        int $priority = PheanstalkPublisherInterface::DEFAULT_PRIORITY,
        int $delay = PheanstalkPublisherInterface::DEFAULT_DELAY
    ): void {
        $this->subscriber->release($job, $priority, $delay);
    }

    public function reserve(): Job
    {
        return $this->subscriber->reserve();
    }

    public function reserveJob(JobIdInterface $job): Job
    {
        return $this->subscriber->reserveJob($job);
    }

    public function reserveWithTimeout(int $timeout): ?Job
    {
        return $this->subscriber->reserveWithTimeout($timeout);
    }

    public function touch(JobIdInterface $job): void
    {
        $this->subscriber->touch($job);
    }

    public function watch(TubeName $tube): int
    {
        return $this->subscriber->watch($tube);
    }
}
