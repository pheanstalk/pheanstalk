<?php

namespace Pheanstalk\Contract;

/**
 * A command to be sent to the beanstalkd server, and response processing logic.
 *
 * @author  Paul Annesley
 */
interface CommandInterface
{
    public const COMMAND_PUT = 'put';
    public const COMMAND_USE = 'use';
    public const COMMAND_RESERVE = 'reserve';
    public const COMMAND_DELETE = 'delete';
    public const COMMAND_RELEASE = 'release';
    public const COMMAND_BURY = 'bury';
    public const COMMAND_WATCH = 'watch';
    public const COMMAND_IGNORE = 'ignore';
    public const COMMAND_PEEK = 'peek';
    public const COMMAND_KICK = 'kick';
    public const COMMAND_STATS_JOB = 'stats-job';
    public const COMMAND_STATS = 'stats';
    public const COMMAND_LIST_TUBES = 'list-tubes';
    public const COMMAND_LIST_TUBE_USED = 'list-tube-used';
    public const COMMAND_LIST_TUBES_WATCHED = 'list-tubes-watched';

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
