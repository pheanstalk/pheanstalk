<?php

declare(strict_types=1);

namespace Pheanstalk\Exception;

use Pheanstalk\Contract\JobIdInterface;

/**
 * Indicates that the given job body was buried due to the server being OOM
 */
final class JobBuriedException extends ClientException
{
    public function __construct(public readonly JobIdInterface $jobId)
    {
        parent::__construct();
    }
}
