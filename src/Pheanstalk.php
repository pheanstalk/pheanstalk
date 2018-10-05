<?php

namespace Pheanstalk;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Response\ArrayResponse;

/**
 * Pheanstalk is a PHP client for the beanstalkd workqueue.
 *
 * The Pheanstalk class is a simple facade for the various underlying components.
 *
 * @see http://github.com/kr/beanstalkd
 * @see http://xph.us/software/beanstalkd/
 *
 * @author  Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk implements PheanstalkInterface
{
    private $_connection;
    private $_using = PheanstalkInterface::DEFAULT_TUBE;
    private $_watching = array(PheanstalkInterface::DEFAULT_TUBE => true);

    /**
     * @param string $host
     * @param int $port
     * @param int $connectTimeout
     * @param bool $connectPersistent
     */
    public function __construct(
        $host,
        $port = PheanstalkInterface::DEFAULT_PORT,
        $connectTimeout = null,
        $connectPersistent = false
    ) {
        $this->setConnection(new Connection($host, $port, $connectTimeout, $connectPersistent));
    }

    /**
     * {@inheritdoc}
     */
    public function setConnection(Connection $connection)
    {
        $this->_connection = $connection;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    // ----------------------------------------

    /**
     * {@inheritdoc}
     */
    public function bury(JobIdInterface $job, int $priority = PheanstalkInterface::DEFAULT_PRIORITY): void
    {
        $this->_dispatch(new Command\BuryCommand($job, $priority));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(JobIdInterface $job): void
    {
        $this->_dispatch(new Command\DeleteCommand($job));
    }

    /**
     * {@inheritdoc}
     */
    public function ignore(string $tube): PheanstalkInterface
    {
        if (isset($this->_watching[$tube])) {
            $this->_dispatch(new Command\IgnoreCommand($tube));
            unset($this->_watching[$tube]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function kick(int $max): int
    {
        $response = $this->_dispatch(new Command\KickCommand($max));

        return $response['kicked'];
    }

    /**
     * {@inheritdoc}
     */
    public function kickJob(JobIdInterface $job): void
    {
        $this->_dispatch(new Command\KickJobCommand($job));
    }

    /**
     * {@inheritdoc}
     */
    public function listTubes(): array
    {
        return (array)$this->_dispatch(
            new Command\ListTubesCommand()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function listTubesWatched(bool $askServer = false): array
    {
        if ($askServer) {
            $response = (array)$this->_dispatch(
                new Command\ListTubesWatchedCommand()
            );
            $this->_watching = array_fill_keys($response, true);
        }

        return array_keys($this->_watching);
    }

    /**
     * {@inheritdoc}
     */
    public function listTubeUsed(bool $askServer = false): string
    {
        if ($askServer) {
            $response = $this->_dispatch(
                new Command\ListTubeUsedCommand()
            );
            $this->_using = $response['tube'];
        }

        return $this->_using;
    }

    /**
     * {@inheritdoc}
     */
    public function pauseTube(string $tube, int $delay): PheanstalkInterface
    {
        $this->_dispatch(new Command\PauseTubeCommand($tube, $delay));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resumeTube(string $tube): PheanstalkInterface
    {
        // Pause a tube with zero delay will resume the tube
        $this->pauseTube($tube, 0);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function peek(JobIdInterface $job): Job
    {
        $response = $this->_dispatch(
            new Command\PeekJobCommand($job)
        );

        return new Job($response['id'], $response['jobdata']);
    }

    /**
     * {@inheritdoc}
     */
    public function peekReady(?string $tube = null): Job
    {
        if ($tube !== null) {
            $this->useTube($tube);
        }

        $response = $this->_dispatch(
            new Command\PeekCommand(Command\PeekCommand::TYPE_READY)
        );

        return new Job($response['id'], $response['jobdata']);
    }

    /**
     * {@inheritdoc}
     */
    public function peekDelayed(?string $tube = null): Job
    {
        if ($tube !== null) {
            $this->useTube($tube);
        }

        $response = $this->_dispatch(
            new Command\PeekCommand(Command\PeekCommand::TYPE_DELAYED)
        );

        return new Job($response['id'], $response['jobdata']);
    }

    /**
     * {@inheritdoc}
     */
    public function peekBuried(?string $tube = null): Job
    {
        if ($tube !== null) {
            $this->useTube($tube);
        }

        $response = $this->_dispatch(
            new Command\PeekCommand(Command\PeekCommand::TYPE_BURIED)
        );

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
        $response = $this->_dispatch(
            new Command\PutCommand($data, $priority, $delay, $ttr)
        );

        return new Job($response['id'], $data);
    }

    /**
     * {@inheritdoc}
     */
    public function putInTube(
        string $tube,
        string $data,
        int $priority = PheanstalkInterface::DEFAULT_PRIORITY,
        int $delay = PheanstalkInterface::DEFAULT_DELAY,
        int $ttr = PheanstalkInterface::DEFAULT_TTR
    ): Job {
        $this->useTube($tube);

        return $this->put($data, $priority, $delay, $ttr);
    }

    /**
     * {@inheritdoc}
     */
    public function release(
        JobIdInterface $job,
        int $priority = PheanstalkInterface::DEFAULT_PRIORITY,
        int $delay = PheanstalkInterface::DEFAULT_DELAY
    ): void {
        $this->_dispatch(
            new Command\ReleaseCommand($job, $priority, $delay)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function reserve(): Job
    {
        $response = $this->_dispatch(
            new Command\ReserveCommand()
        );

        return new Job($response['id'], $response['jobdata']);
    }

    /**
     * {@inheritdoc}
     */
    public function reserveWithTimeout(int $timeout): ?Job
    {
        $response = $this->_dispatch(
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
        return $this->_dispatch(new Command\StatsJobCommand($job));
    }

    /**
     * {@inheritdoc}
     */
    public function statsTube(string $tube): ResponseInterface
    {
        return $this->_dispatch(new Command\StatsTubeCommand($tube));
    }

    /**
     * {@inheritdoc}
     */
    public function stats(): ResponseInterface
    {
        return $this->_dispatch(new Command\StatsCommand());
    }

    /**
     * {@inheritdoc}
     */
    public function touch(JobIdInterface $job): void
    {
        $this->_dispatch(new Command\TouchCommand($job));
    }

    /**
     * {@inheritdoc}
     */
    public function useTube(string $tube): PheanstalkInterface
    {
        if ($this->_using !== $tube) {
            $this->_dispatch(new Command\UseCommand($tube));
            $this->_using = $tube;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function watch(string $tube): PheanstalkInterface
    {
        if (!isset($this->_watching[$tube])) {
            $this->_dispatch(new Command\WatchCommand($tube));
            $this->_watching[$tube] = true;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function watchOnly(string $tube): PheanstalkInterface
    {
        $this->watch($tube);

        $ignoreTubes = array_diff_key($this->_watching, array($tube => true));
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
    private function _dispatch($command)
    {
        try {
            $response = $this->_connection->dispatchCommand($command);
        } catch (Exception\SocketException $e) {
            $this->_reconnect();
            $response = $this->_connection->dispatchCommand($command);
        }

        return $response;
    }

    /**
     * Creates a new connection object, based on the existing connection object,
     * and re-establishes the used tube and watchlist.
     */
    private function _reconnect()
    {
        $new_connection = new Connection(
            $this->_connection->getHost(),
            $this->_connection->getPort(),
            $this->_connection->getConnectTimeout()
        );

        $this->setConnection($new_connection);

        if ($this->_using != PheanstalkInterface::DEFAULT_TUBE) {
            $tube = $this->_using;
            $this->_using = null;
            $this->useTube($tube);
        }

        foreach ($this->_watching as $tube => $true) {
            if ($tube != PheanstalkInterface::DEFAULT_TUBE) {
                unset($this->_watching[$tube]);
                $this->watch($tube);
            }
        }

        if (!isset($this->_watching[PheanstalkInterface::DEFAULT_TUBE])) {
            $this->ignore(PheanstalkInterface::DEFAULT_TUBE);
        }
    }


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

    public function withWatchedTube(string $tube, \Closure $closure)
    {
        $watched = $this->listTubesWatched();
        try {
            $this->watchOnly($tube);
            return $closure($this);
        } finally {
            foreach($watched as $tube) {
                $this->watch($tube);
            }
            if (!in_array($tube, $watched)) {
                $this->ignore($tube);
            }

        }

    }

}
