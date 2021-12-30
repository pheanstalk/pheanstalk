<?php

declare(strict_types=1);

namespace Pheanstalk\Contract;

/**
 * A response from the beanstalkd server.
 */
interface JobResponseInterface extends JobIdResponseInterface
{
    public function getData(): string;
}
