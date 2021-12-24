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
use Pheanstalk\Parser\ExceptionParser;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseType;

/**
 * The 'ignore' command.
 * Removes a tube from the watch list to reserve jobs from.
 */
class IgnoreCommand extends TubeCommand
{
    public function getType(): CommandType
    {
        return CommandType::IGNORE;
    }

    public function getResponseParser(): ResponseParserInterface
    {
        return new ChainedParser(
            new ExceptionParser(ResponseType::NOT_IGNORED, new Exception\NotIgnoredException()),
            new EmptySuccessParser(ResponseType::WATCHING)
        );
    }
}
