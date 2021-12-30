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

    private readonly JobIdInterface $id;
    public function __construct(
        JobIdInterface|int|string $id,
        private readonly string $data
    ) {
        $this->id = new JobId($id);
    }

    /**
     * The job ID, unique on the beanstalkd server.
     */
    public function getId(): string
    {
        return $this->id->getId();
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
