<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Parser\EmptySuccessParser;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseType;

/**
 * The 'use' command.
 *
 * The "use" command is for producers. Subsequent put commands will put jobs into
 * the tube specified by this command. If no use command has been issued, jobs
 * will be put into the tube named "default".
 */
class UseCommand extends TubeCommand
{
    public function getType(): CommandType
    {
        return CommandType::USE;
    }

    public function getResponseParser(): ResponseParserInterface
    {
        return new EmptySuccessParser(ResponseType::USING);
    }
}
