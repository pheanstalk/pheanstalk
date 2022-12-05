<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\PheanstalkManagerInterface;
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
 * This class implements "management" functions for the beanstalk protocol.
 *
 */
final class PheanstalkManager implements PheanstalkManagerInterface
{
    use StaticFactoryTrait;

    public function kick(int $max): int
    {
        $command = new Command\KickCommand($max);
        return $command->interpret($this->dispatch($command));
    }

    public function kickJob(JobIdInterface $job): void
    {
        $command = new Command\KickJobCommand($job);
        $command->interpret($this->dispatch($command));
    }

    private function dispatch(CommandInterface $command): RawResponse
    {
        return $this->connection->dispatchCommand($command);
    }

    public function listTubes(): TubeList
    {
        $command = new Command\ListTubesCommand();
        return $command->interpret($this->dispatch($command));
    }




    public function pauseTube(TubeName $tube, int $delay): void
    {
        $command = new Command\PauseTubeCommand($tube, $delay);
        $command->interpret($this->dispatch($command));
    }

    public function resumeTube(TubeName $tube): void
    {
        // Pause a tube with zero delay will resume the tube
        $this->pauseTube($tube, 0);
    }

    public function peek(JobIdInterface $job): Job
    {
        $command = new Command\PeekJobCommand($job);
        return $command->interpret($this->dispatch($command));
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
        $response = $command->interpret($this->dispatch($command));
        return $response instanceof Success ? null : $response;
    }

    public function peekBuried(): null|Job
    {
        return $this->peekState(JobState::BURIED);
    }





    public function statsJob(JobIdInterface $job): JobStats
    {
        $command = new Command\StatsJobCommand($job);
        return $command->interpret($this->dispatch($command));
    }

    public function statsTube(TubeName $tube): TubeStats
    {
        $command = new Command\StatsTubeCommand($tube);
        return $command->interpret($this->dispatch($command));
    }



    public function stats(): ServerStats
    {
        $command = new Command\StatsCommand();
        return $command->interpret($this->dispatch($command));
    }
}
