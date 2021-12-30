<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\CommandWithDataInterface;
use Pheanstalk\Exception;
use Pheanstalk\JobId;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;

/**
 * The 'put' command.
 *
 * Inserts a job into the client's currently used tube.
 *
 * @see UseCommand
 */
final class PutCommand implements CommandInterface, CommandWithDataInterface
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

    public function getData(): string
    {
        return $this->data;
    }

    private function getDataLength(): int
    {
        return mb_strlen($this->data, '8bit');
    }

    public function interpret(RawResponse $response): JobId
    {
        if ($response->type === ResponseType::Inserted && isset($response->argument)) {
            return new JobId($response->argument);
        } elseif ($response->type === ResponseType::Buried && isset($response->argument)) {
            throw new Exception\JobBuriedException(new JobId($response->argument));
        }
        return match ($response->type) {
            ResponseType::Buried => throw Exception\MalformedResponseException::expectedIntegerArgument(),
            ResponseType::ExpectedCrlf => throw new Exception\ExpectedCrlfException(),
            ResponseType::Draining => throw new Exception\ServerDrainingException(),
            ResponseType::JobTooBig => throw new Exception\JobTooBigException(),
            ResponseType::Inserted => throw Exception\MalformedResponseException::expectedData(),
            default => throw new Exception\UnsupportedResponseException($response->type)
        };
    }
}
