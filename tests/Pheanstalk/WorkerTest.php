<?php

namespace Pheanstalk;


class WorkerTest extends \PHPUnit_Framework_TestCase
{
    const SERVER_HOST = 'localhost';
    const SERVER_PORT = '11300';

    /**
     * @throws Exception\WorkerException
     */
    public function testWorkerRuns()
    {
        $testWorkerRuns = $this;
        $tube = 'worker_tube_' . rand(53, 504);
        $data = 'worker_value_' . rand(95, 3000);

        $pheanstalk = new Pheanstalk(self::SERVER_HOST, self::SERVER_PORT);
        $worker = new Worker(self::SERVER_HOST, self::SERVER_PORT);

        $job = $pheanstalk->useTube($tube)
            ->put($data);

        $worker->register($tube, function(Job $job) use ($testWorkerRuns, $data) {
            $testWorkerRuns->assertEquals($data, $job->getData());
        });

        $processedJob = $worker->processOne(0);

        $stats = $pheanstalk->statsTube($tube);
        $this->assertEquals($stats['total-jobs'], 1);
    }

    /**
     * When the timeout passed to ::processOne() is 0 and there are no jobs it should return quitely.
     * @throws Exception\WorkerException
     */
    public function testWorkerNoJobs()
    {
        $testWorkerRuns = $this;
        $tube = 'worker_tube_' . rand(53, 504);
        $data = 'worker_value_' . rand(95, 3000);

        $pheanstalk = new Pheanstalk(self::SERVER_HOST, self::SERVER_PORT);
        $worker = new Worker(self::SERVER_HOST, self::SERVER_PORT);

        $worker->register($tube, function(Job $job) use ($testWorkerRuns, $data) {
            $testWorkerRuns->assertEquals($data, $job->getData());
        });

        $processedJob = $worker->processOne(0);

        $stats = $pheanstalk->statsTube($tube);
        $this->assertEquals($stats['total-jobs'], 0);
    }
}