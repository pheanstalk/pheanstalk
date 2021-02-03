<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Exception\ServerOutOfMemoryException;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseLine;

/**
 * The 'put' command.
 *
 * Inserts a job into the client's currently used tube.
 *
 * @see UseCommand
 */
class PutCommand extends AbstractCommand implements ResponseParserInterface
{
    private $data;
    private $priority;
    private $delay;
    private $ttr;

    /**
     * Puts a job on the queue.
     *
     * @param string $data     The job data
     * @param int    $priority From 0 (most urgent) to 0xFFFFFFFF (least urgent)
     * @param int    $delay    Seconds to wait before job becomes ready
     * @param int    $ttr      Time To Run: seconds a job can be reserved for
     */
    public function __construct(string $data, int $priority, int $delay, int $ttr)
    {
        $this->data = $data;
        $this->priority = $priority;
        $this->delay = $delay;
        $this->ttr = $ttr;
    }

    public function getCommandLine(): string
    {
        return sprintf(
            'put %u %u %u %u',
            $this->priority,
            $this->delay,
            $this->ttr,
            $this->getDataLength()
        );
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

    public function parseResponse(\Pheanstalk\ResponseLine $responseLine, ?string $responseData): \Pheanstalk\Contract\ResponseInterface
    {
        return match($responseLine->getName()) {
            ResponseInterface::RESPONSE_INSERTED => $this->createResponse($responseLine->getName(), ['id' => (int) $responseLine->getArguments()]),
            ResponseInterface::RESPONSE_BURIED => throw new ServerOutOfMemoryException(sprintf(
        '%s: server ran out of memory trying to grow the priority queue data structure.',
                $responseLine
            )),
            ResponseInterface::RESPONSE_JOB_TOO_BIG => throw new Exception\JobTooBigException(sprintf(
                '%s: job data exceeds server-enforced limit',
                $responseLine
            )),
            ResponseInterface::RESPONSE_EXPECTED_CRLF => throw new Exception\ClientBadFormatException(sprintf(
                '%s: CRLF expected',
                $responseLine
            )),

        };
    }
}
