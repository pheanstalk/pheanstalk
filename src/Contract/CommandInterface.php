<?php

namespace Pheanstalk\Contract;

/**
 * A command to be sent to the beanstalkd server, and response processing logic.
 *
 * @author  Paul Annesley
 */
interface CommandInterface
{
    const COMMAND_PUT = 'put';
    const COMMAND_USE = 'use';
    const COMMAND_RESERVE = 'reserve';
    const COMMAND_DELETE = 'delete';
    const COMMAND_RELEASE = 'release';
    const COMMAND_BURY = 'bury';
    const COMMAND_WATCH = 'watch';
    const COMMAND_IGNORE = 'ignore';
    const COMMAND_PEEK = 'peek';
    const COMMAND_KICK = 'kick';
    const COMMAND_STATS_JOB = 'stats-job';
    const COMMAND_STATS = 'stats';
    const COMMAND_LIST_TUBES = 'list-tubes';
    const COMMAND_LIST_TUBE_USED = 'list-tube-used';
    const COMMAND_LIST_TUBES_WATCHED = 'list-tubes-watched';

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
