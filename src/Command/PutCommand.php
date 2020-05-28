<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Response\ArrayResponse;

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

    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        if (preg_match('#^INSERTED (\d+)$#', $responseLine, $matches)) {
            return $this->createResponse('INSERTED', [
                'id' => (int) $matches[1],
            ]);
        } elseif (preg_match('#^BURIED (\d)+$#', $responseLine, $matches)) {
            throw new Exception\ServerOutOfMemoryException(sprintf(
                '%s: server ran out of memory trying to grow the priority queue data structure.',
                $responseLine
            ));
        } elseif (preg_match('#^JOB_TOO_BIG$#', $responseLine)) {
            throw new Exception\JobTooBigException(sprintf(
                '%s: job data exceeds server-enforced limit',
                $responseLine
            ));
        } elseif (preg_match('#^EXPECTED_CRLF#', $responseLine)) {
            throw new Exception\ClientBadFormatException(sprintf(
                '%s: CRLF expected',
                $responseLine
            ));
        } elseif (preg_match('#^DRAINING#', $responseLine)) {
            throw new Exception\ServerDrainingException(sprintf(
                '%s: server is in drain mode and no longer accepting new jobs',
                $responseLine
            ));
        } else {
            throw new Exception(sprintf(
                'Unhandled response: %s',
                $responseLine
            ));
        }
    }
}
