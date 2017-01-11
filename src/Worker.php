<?php

namespace Pheanstalk;

/**
 * Default implementation of a php worker.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Worker
{
    private $_pheanstalk;
    private $_callbacks = array();

    /**
     * @param string $host
     * @param int $port
     * @param int $connectTimeout
     */
    public function __construct($host, $port = PheanstalkInterface::DEFAULT_PORT, $connectTimeout = null)
    {

        $this->_pheanstalk = new Pheanstalk($host, $port, $connectTimeout);
//        $this->_pheanstalk->ignore('default'); // ignore the default queue as we have no callable for it.
    }

    /**
     * @param $tube
     * @param callable $function
     * @param string $onError
     */
    public function register($tube, callable $callable, $onError = '')
    {
        $this->_callbacks[$tube] = array(
            'callable' => $callable,
            'onError' => $onError,
        );
        $this->_pheanstalk->watch($tube);
    }

    /**
     * Process jobs forever.
     */
    public function process()
    {
        while (1) {
            $this->processOne();
        }
    }

    /**
     * Reserve the next job and process.
     *
     * @param $timeout
     *  Sets how long the reserve process will wait for a job.
     * @return bool|object|Job
     *  returns the job which was just processed.
     * @throws Exception\WorkerException
     *  if a job is reserved which has no registered callable then throw this error and stop the processing. This should
     *  never occur as we are only watching tubes with registered handlers.
     */
    public function processOne($timeout = null)
    {
        static $firstRun = TRUE;

        if ($firstRun) {
            // if there is no callback for the "default" tube then ignore it.
            if (!isset($this->_callbacks['default'])) {
                $this->_pheanstalk->ignore('default');
            }
        }

        $job = $this->_pheanstalk->reserve($timeout);
        // Only process the job if a job is returned.
        if ($job) {
            // Get the job stats so we know which tube this was received from.
            $statJob = $this->_pheanstalk->statsJob($job);
            $tube = $statJob['tube'];

            if (isset($this->_callbacks[$tube])) {
                try {
                    $this->_callbacks[$tube]['callable']($job);
                    $this->_pheanstalk->delete($job);
                } catch (Exception $e) {
                    if (!empty($this->_callbacks[$tube]['onError']) && is_a($e, $this->_callbacks['onError'])) {
                        $this->_pheanstalk->release($job);
                    } else {
                        $this->_pheanstalk->bury($job);
                    }
                }
            } else {
                throw new Exception\WorkerException(sprintf(
                    'Job fetched for unknown tube "%s"',
                    $tube
                ));
            }
        }

        return $job;
    }
}