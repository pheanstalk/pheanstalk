<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\JobCommandTemplate;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\Success;

/**
 * The 'delete' command.
 * Permanently deletes an already-reserved job.
 */
final class DeleteCommand extends JobCommand
{
    public function interpret(RawResponse $response): Success
    {
        return match ($response->type) {
            ResponseType::NotFound => throw new JobNotFoundException(),
            ResponseType::Deleted => new Success(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }

    protected function getCommandTemplate(): JobCommandTemplate
    {
        return new JobCommandTemplate("delete {id}");
    }
}
