<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\JobCommandTemplate;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\Success;

/**
 * The 'touch' command.
 * The 'touch' command allows a worker to request more time to work on a job.
 * This is useful for jobs that potentially take a long time, but you still want
 * the benefits of a TTR pulling a job away from an unresponsive worker.  A worker
 * may periodically tell the server that it's still alive and processing a job
 * (e.g. it may do this on DEADLINE_SOON).
 *
 */
final class TouchCommand extends JobCommand
{
    public function interpret(RawResponse $response): Success
    {
        return match ($response->type) {
            ResponseType::NotFound => throw new Exception\JobNotFoundException(),
            ResponseType::Touched => new Success(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }

    protected function getCommandTemplate(): JobCommandTemplate
    {
        return new JobCommandTemplate("touch {id}");
    }
}
