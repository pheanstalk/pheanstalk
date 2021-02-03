<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Contract\JobIdInterface;

/**
 * A job in a beanstalkd server.
 */
class Job implements JobIdInterface
{
    public const STATUS_READY = 'ready';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_DELAYED = 'delayed';
    public const STATUS_BURIED = 'buried';

    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $data;

    /**
     * @param int    $id   The job ID
     * @param string $data The job data
     */
    public function __construct(int $id, string $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * The job ID, unique on the beanstalkd server.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
