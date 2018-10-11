<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\CommandException;
use Pheanstalk\Response\ArrayResponse;

/**
 * Common functionality for Command implementations.
 */
abstract class AbstractCommand implements CommandInterface
{
    public function hasData(): bool
    {
        return false;
    }

    public function getData(): string
    {
        throw new CommandException('Command has no data');
    }

    public function getDataLength(): int
    {
        throw new CommandException('Command has no data');
    }

    public function getResponseParser(): ResponseParserInterface
    {
        if ($this instanceof ResponseParserInterface) {
            return $this;
        }
        throw new \RuntimeException('Concrete implementation must implement `ResponseParser` or override this method');
    }

    /**
     * Creates a Response for the given data.
     */
    protected function createResponse(string $name, array $data = []): ArrayResponse
    {
        return new ArrayResponse($name, $data);
    }
}
