<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Parser\ChainedParser;
use Pheanstalk\Parser\JobNotFoundExceptionParser;
use Pheanstalk\Parser\JobParser;
use Pheanstalk\ResponseType;

/**
 * The 'peek', 'peek-ready', 'peek-delayed' and 'peek-buried' commands.
 *
 * The peek commands let the client inspect a job in the system. There are four
 * variations. All but the first (peek) operate only on the currently used tube.
 */
class PeekCommand extends AbstractCommand
{
    public function __construct(private readonly CommandType $type)
    {
        if (!in_array($this->type, [CommandType::PEEK_BURIED, CommandType::PEEK_DELAYED, CommandType::PEEK_READY])) {
            throw new \InvalidArgumentException("Unsupported command type: {$type->name} for PeekCommand");
        }
    }

    public function getCommandLine(): string
    {
        return $this->getType()->value;
    }

    public function getResponseParser(): ResponseParserInterface
    {
        return new ChainedParser(
            new JobNotFoundExceptionParser(),
            new JobParser(ResponseType::FOUND),

        );
    }

    public function getType(): CommandType
    {
        return $this->type;
    }
}
