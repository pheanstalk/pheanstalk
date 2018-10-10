<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\JobIdInterface;

/**
 * A command that is executed against a single job
 */
abstract class JobCommand extends AbstractCommand
{
    protected $jobId;

    public function __construct(JobIdInterface $subject)
    {
        $this->jobId = $subject->getId();
    }
}
