<?php

declare(strict_types=1);

namespace Pheanstalk\Contract;

use Pheanstalk\CommandType;

/**
 * A command to be sent to the beanstalkd server, and response processing logic.
 */
interface CommandInterface
{
    public function getType(): CommandType;
    /**
     * The command line, without trailing CRLF.
     */
    public function getCommandLine(): string;

    /**
     * Whether the command is followed by data.
     */
    public function hasData(): bool;

    /**
     * The binary data to follow the command.
     */
    public function getData(): string;

    /**
     * The length of the binary data in bytes.
     */
    public function getDataLength(): int;

    /**
     * The response parser for the command.
     */
    public function getResponseParser(): ResponseParserInterface;
}
