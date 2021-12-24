<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Parser\ChainedParser;
use Pheanstalk\Parser\ExceptionParser;
use Pheanstalk\Parser\JobParser;
use Pheanstalk\ResponseType;

/**
 * The 'put' command.
 *
 * Inserts a job into the client's currently used tube.
 *
 * @see UseCommand
 */
class PutCommand extends AbstractCommand
{
    /**
     * Puts a job on the queue.
     *
     * @param string $data     The job data
     * @param int    $priority From 0 (most urgent) to 0xFFFFFFFF (least urgent)
     * @param int    $delay    Seconds to wait before job becomes ready
     * @param int    $ttr      Time To Run: seconds a job can be reserved for
     */
    public function __construct(private readonly string $data, private readonly int $priority, private readonly int $delay, private readonly int $ttr)
    {
    }

    public function getCommandLine(): string
    {
        return "put {$this->priority} {$this->delay} {$this->ttr} {$this->getDataLength()}";
    }

    public function hasData(): bool
    {
        return true;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getDataLength(): int
    {
        return mb_strlen($this->data, '8bit');
    }

    public function getResponseParser(): ResponseParserInterface
    {
        return new ChainedParser(
            new ExceptionParser(ResponseType::BURIED, new Exception\JobBuriedException()),
            new ExceptionParser(ResponseType::EXPECTED_CRLF, new Exception\ExpectedCrlfException()),
            new ExceptionParser(ResponseType::DRAINING, new Exception\ServerDrainingException()),
            new ExceptionParser(ResponseType::JOB_TOO_BIG, new Exception\JobTooBigException()),
            new JobParser(ResponseType::INSERTED),

        );
    }

    public function getType(): CommandType
    {
        return CommandType::PUT;
    }
}
