<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Parser\ChainedParser;
use Pheanstalk\Parser\EmptySuccessParser;
use Pheanstalk\Parser\TubeNotFoundExceptionParser;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\TubeName;

/**
 * The 'pause-tube' command.
 *
 * Temporarily prevent jobs being reserved from the given tube.
 */
class PauseTubeCommand extends TubeCommand
{
    /**
     * @param int $delay Seconds before jobs may be reserved from this queue.
     */
    public function __construct(TubeName $tube, private readonly int $delay)
    {
        parent::__construct($tube);
    }

    public function getCommandLine(): string
    {
        return "pause-tube {$this->tube} {$this->delay}";
    }

    public function getResponseParser(): ResponseParserInterface
    {
        return new ChainedParser(
            new TubeNotFoundExceptionParser(),
            new EmptySuccessParser(ResponseType::PAUSED)
        );
    }

    public function getType(): CommandType
    {
        return CommandType::PAUSE_TUBE;
    }
}
