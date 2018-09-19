<?php

namespace Pheanstalk;

use Pheanstalk\Contract\JobIdInterface;

/**
 * A job in a beanstalkd server.
 *
 * @author  Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class Job implements JobIdInterface
{
    const STATUS_READY = 'ready';
    const STATUS_RESERVED = 'reserved';
    const STATUS_DELAYED = 'delayed';
    const STATUS_BURIED = 'buried';

    private $_id;
    private $data;

    /**
     * @param int    $id   The job ID
     * @param string $data The job data
     */
    public function __construct(int $id, string $data)
    {
        $this->_id = $id;
        $this->data = $data;
    }

    /**
     * The job ID, unique on the beanstalkd server.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->_id;
    }

    /**
     * The job data.
     *
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }
}
