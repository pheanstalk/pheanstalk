<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\Success;

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

    protected function getCommandTemplate(): string
    {
        return "delete {id}";
    }
}
