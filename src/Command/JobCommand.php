<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Values\JobCommandTemplate;

/**
 * A command that is executed against a single job
 * @internal
 */
abstract class JobCommand implements CommandInterface
{
    abstract protected function getCommandTemplate(): JobCommandTemplate;

    final public function getCommandLine(): string
    {
        return $this->getCommandTemplate()->render($this->jobId);
    }

    public function __construct(private readonly JobIdInterface $jobId)
    {
    }
}
