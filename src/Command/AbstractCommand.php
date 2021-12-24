<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\CommandException;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseType;

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

    /**
     * Creates a Response for the given data.
     */
    protected function createResponse(ResponseType $responseType, array $data = []): ArrayResponse
    {
        return new ArrayResponse($responseType, $data);
    }

    public function getCommandLine(): string
    {
        return $this->getType()->value;
    }
}
