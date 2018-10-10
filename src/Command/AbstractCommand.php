<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\CommandException;
use Pheanstalk\Response\ArrayResponse;

/**
 * Common functionality for Command implementations.
 *
 * @author  Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
abstract class AbstractCommand implements CommandInterface
{
    /* (non-phpdoc)
     * @see Command::hasData()
     */
    public function hasData(): bool
    {
        return false;
    }

    /* (non-phpdoc)
     * @see Command::getData()
     */
    public function getData(): string
    {
        throw new CommandException('Command has no data');
    }

    /* (non-phpdoc)
     * @see Command::getDataLength()
     */
    public function getDataLength(): int
    {
        throw new CommandException('Command has no data');
    }

    /* (non-phpdoc)
     * @see Command::getResponseParser()
     */
    public function getResponseParser(): ResponseParserInterface
    {
        if (!$this instanceof ResponseParserInterface) {
            throw new \RuntimeException('Concrete implementation must implement `ResponseParser` or override this method');
        }
        return $this;
    }

    /**
     * The string representation of the object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getCommandLine();
    }

    // ----------------------------------------
    // protected

    /**
     * Creates a Response for the given data.
     *
     * @param array
     *
     */
    protected function createResponse(string $name, array $data = []): ArrayResponse
    {
        return new ArrayResponse($name, $data);
    }
}
