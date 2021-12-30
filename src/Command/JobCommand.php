<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\JobIdInterface;

/**
 * A command that is executed against a single job
 */
abstract class JobCommand implements CommandInterface
{
    /**
     * @return string A template for generating the command
     */
    abstract protected function getCommandTemplate(): string;

    final public function getCommandLine(): string
    {
        return strtr($this->getCommandTemplate(), ['{id}' => $this->jobId->getId()]);
    }

    public function __construct(private readonly JobIdInterface $jobId)
    {
    }
}
