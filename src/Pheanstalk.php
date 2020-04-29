<?php

namespace Pheanstalk;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Exception\DeadlineSoonException;

/**
 * Pheanstalk is a PHP client for the beanstalkd workqueue.
 */
class Pheanstalk implements PheanstalkInterface
{
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var ?string
     */
    private $using = PheanstalkInterface::DEFAULT_TUBE;
    /**
     * @var array<string,bool>
     */
    private $watching = [PheanstalkInterface::DEFAULT_TUBE => true];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Static constructor that uses autodetection to choose an underlying socket implementation
     * @param string $host
     * @param int $port
     * @param int $connectTimeout
     * @return Pheanstalk
     */
    public static function create(string $host, int $port = 11300, int $connectTimeout = 10)
    {
        return self::createWithFactory(new SocketFactory($host, $port, $connectTimeout));
    }

    /**
     * Static constructor that uses a given socket factory for underlying connections
     * @param SocketFactoryInterface $factory
     * @return Pheanstalk
     */
    public static function createWithFactory(SocketFactoryInterface $factory)
    {
        return new self(new Connection($factory));
    }

    // ----------------------------------------

    /**
     * {@inheritdoc}
     */
    public function bury(JobIdInterface $job, int $priority = PheanstalkInterface::DEFAULT_PRIORITY): void
    {
        $this->dispatch(new Command\BuryCommand($job, $priority));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(JobIdInterface $job): void
    {
        $this->dispatch(new Command\DeleteCommand($job));
    }

    /**
     * {@inheritdoc}
     */
    public function ignore(string $tube): PheanstalkInterface
    {
        if (isset($this->watching[$tube])) {
            $this->dispatch(new Command\IgnoreCommand($tube));
            unset($this->watching[$tube]);
        }

        return $this;
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

    /**
     * {@inheritdoc}
     */
    public function listTubesWatched(bool $askServer = false): array
    {
        if ($askServer) {
            $response = (array)$this->dispatch(
                new Command\ListTubesWatchedCommand()
            );
            $this->watching = array_fill_keys($response, true);
        }

        return array_keys($this->watching);
    }

    /**
     * {@inheritdoc}
     */
    public function listTubeUsed(bool $askServer = false): string
    {
        if ($askServer) {
            $response = $this->dispatch(
                new Command\ListTubeUsedCommand()
            );
            $this->using = $response['tube'];
        }

        return $this->using;
    }

    /**
     * {@inheritdoc}
     */
    public function pauseTube(string $tube, int $delay): void
    {
        $this->dispatch(new Command\PauseTubeCommand($tube, $delay));
    }

    /**
     * {@inheritdoc}
     */
    public function resumeTube(string $tube): void
    {
        // Pause a tube with zero delay will resume the tube
        $this->pauseTube($tube, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function peek(JobIdInterface $job): Job
    {
        $response = $this->dispatch(
            new Command\PeekJobCommand($job)
        );

        return new Job($response['id'], $response['jobdata']);
    }

    /**
     * {@inheritdoc}
     */
    public function peekReady(): ?Job
    {
        $response = $this->dispatch(
            new Command\PeekCommand(Command\PeekCommand::TYPE_READY)
        );
        if ($response->getResponseName() === ResponseInterface::RESPONSE_NOT_FOUND) {
            return null;
        }

        return new Job($response['id'], $response['jobdata']);
    }

    /**
     * {@inheritdoc}
     */
    public function peekDelayed(): ?Job
    {
        $response = $this->dispatch(
            new Command\PeekCommand(Command\PeekCommand::TYPE_DELAYED)
        );
        if ($response->getResponseName() === ResponseInterface::RESPONSE_NOT_FOUND) {
            return null;
        }

        return new Job($response['id'], $response['jobdata']);
    }

    /**
     * {@inheritdoc}
     */
    public function peekBuried(): ?Job
    {
        $response = $this->dispatch(
            new Command\PeekCommand(Command\PeekCommand::TYPE_BURIED)
        );
        if ($response->getResponseName() === ResponseInterface::RESPONSE_NOT_FOUND) {
            return null;
        }

        return new Job($response['id'], $response['jobdata']);
    }

    /**
     * {@inheritdoc}
     */
    public function put(
        string $data,
        int $priority = PheanstalkInterface::DEFAULT_PRIORITY,
        int $delay = PheanstalkInterface::DEFAULT_DELAY,
        int $ttr = PheanstalkInterface::DEFAULT_TTR
    ): Job {
        $response = $this->dispatch(
            new Command\PutCommand($data, $priority, $delay, $ttr)
        );

        return new Job($response['id'], $data);
    }

    /**
     * {@inheritdoc}
     */
    public function release(
        JobIdInterface $job,
        int $priority = PheanstalkInterface::DEFAULT_PRIORITY,
        int $delay = PheanstalkInterface::DEFAULT_DELAY
    ): void {
        $this->dispatch(
            new Command\ReleaseCommand($job, $priority, $delay)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function reserve(): Job
    {
        $response = $this->dispatch(
            new Command\ReserveCommand()
        );

        return new Job($response['id'], $response['jobdata']);
    }

    /**
     * {@inheritdoc}
     */
    public function reserveWithTimeout(int $timeout): ?Job
    {
        $response = $this->dispatch(
            new Command\ReserveWithTimeoutCommand($timeout)
        );

        if ($response->getResponseName() === ResponseInterface::RESPONSE_DEADLINE_SOON) {
            throw new DeadlineSoonException();
        }

        if ($response->getResponseName() === ResponseInterface::RESPONSE_TIMED_OUT) {
            return null;
        }

        return new Job($response['id'], $response['jobdata']);
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
        return $this->dispatch(new Command\StatsTubeCommand($tube));
    }

    /**
     * {@inheritdoc}
     */
    public function stats(): ResponseInterface
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

    /**
     * {@inheritdoc}
     */
    public function useTube(string $tube): PheanstalkInterface
    {
        if ($this->using !== $tube) {
            $this->dispatch(new Command\UseCommand($tube));
            $this->using = $tube;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function watch(string $tube): PheanstalkInterface
    {
        if (!isset($this->watching[$tube])) {
            $this->dispatch(new Command\WatchCommand($tube));
            $this->watching[$tube] = true;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function watchOnly(string $tube): PheanstalkInterface
    {
        $this->watch($tube);

        $ignoreTubes = array_diff_key($this->watching, [$tube => true]);
        foreach ($ignoreTubes as $ignoreTube => $true) {
            $this->ignore($ignoreTube);
        }

        return $this;
    }

    // ----------------------------------------

    /**
     * Dispatches the specified command to the connection object.
     *
     * If a SocketException occurs, the connection is reset, and the command is
     * re-attempted once.
     *
     * @param CommandInterface $command
     *
     * @return ResponseInterface
     */
    private function dispatch($command)
    {
        try {
            $response = $this->connection->dispatchCommand($command);
        } catch (Exception\SocketException $e) {
            $this->reconnect();
            $response = $this->connection->dispatchCommand($command);
        }

        return $response;
    }

    /**
     * Creates a new connection object, based on the existing connection object,
     * and re-establishes the used tube and watchlist.
     */
    private function reconnect()
    {
        $this->connection->disconnect();

        if ($this->using !== PheanstalkInterface::DEFAULT_TUBE) {
            $this->dispatch(new Command\UseCommand($this->using));
        }

        foreach ($this->watching as $tube => $true) {
            if ($tube != PheanstalkInterface::DEFAULT_TUBE) {
                unset($this->watching[$tube]);
                $this->watch($tube);
            }
        }

        if (!isset($this->watching[PheanstalkInterface::DEFAULT_TUBE])) {
            $this->ignore(PheanstalkInterface::DEFAULT_TUBE);
        }
    }

    /**
     * @param string $tube The tube to use during execution
     * @param \Closure $closure Closure to execute while using the specified tube
     * @return mixed the return value of the closure.
     * @internal This is marked as internal since it is not part of a stabilized interface.
     */
    public function withUsedTube(string $tube, \Closure $closure)
    {
        $used = $this->listTubeUsed();
        try {
            $this->useTube($tube);
            return $closure($this);
        } finally {
            $this->useTube($used);
        }
    }

    /**
     * @param string $tube The tube to watch during execution
     * @param \Closure $closure Closure to execute while using the specified tube
     * @return mixed the return value of the closure.
     * @internal This is marked as internal since it is not part of a stabilized interface.
     */
    public function withWatchedTube(string $tube, \Closure $closure)
    {
        $watched = $this->listTubesWatched();
        try {
            $this->watchOnly($tube);
            return $closure($this);
        } finally {
            foreach ($watched as $tube) {
                $this->watch($tube);
            }
            if (!in_array($tube, $watched)) {
                $this->ignore($tube);
            }
        }
    }
}
