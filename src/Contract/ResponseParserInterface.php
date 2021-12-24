<?php

declare(strict_types=1);

namespace Pheanstalk\Contract;

use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseType;

/**
 * A parser for response data sent from the beanstalkd server.
 *
 * @author  Paul Annesley
 */
interface ResponseParserInterface
{
    /**
     * Parses raw response data into a Response object.
     * MUST return `null` if the parser cannot parse the response.
     * MUST throw an exception if the parser determines the response to be invalid
     * @param non-empty-list<string> $arguments The parts of the response line
     */
    public function parseResponse(CommandInterface $command, ResponseType $type, array $arguments = [], null|string $data = null): null|ResponseInterface;
}
