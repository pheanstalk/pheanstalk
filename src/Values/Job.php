<?php

declare(strict_types=1);

namespace Pheanstalk\Values;

use Pheanstalk\Contract\JobIdInterface;

/**
 * A job in a beanstalkd server.
 */
final class Job implements JobIdInterface
{
    private readonly JobIdInterface $id;
    public function __construct(
        JobIdInterface $id,
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
