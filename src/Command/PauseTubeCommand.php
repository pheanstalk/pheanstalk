<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Exception;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\Success;
use Pheanstalk\Values\TubeCommandTemplate;
use Pheanstalk\Values\TubeName;

/**
 * The 'pause-tube' command.
 *
 * Temporarily prevent jobs being reserved from the given tube.
 */
final class PauseTubeCommand extends TubeCommand
{
    /**
     * @param int $delay Seconds before jobs may be reserved from this queue.
     */
    public function __construct(TubeName $tube, private readonly int $delay)
    {
        parent::__construct($tube);
    }

    public function interpret(RawResponse $response): Success
    {
        return match ($response->type) {
            ResponseType::NotFound => throw new Exception\TubeNotFoundException(),
            ResponseType::Paused => new Success(),
            default => throw new UnsupportedResponseException($response->type)
        };
    }

    protected function getCommandTemplate(): TubeCommandTemplate
    {
        return new TubeCommandTemplate("pause-tube {tube} {$this->delay}");
    }
}
