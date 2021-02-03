<?php

declare(strict_types=1);

namespace Pheanstalk\Contract;

use Pheanstalk\Response\ArrayResponse;

/**
 * A parser for response data sent from the beanstalkd server.
 */
interface ResponseParserInterface
{
    /**
     * Parses raw response data into a Response object.
     * @param \Pheanstalk\ResponseLine $responseLine Without trailing CRLF
     * @param string|null $responseData (null if no data)
     * @return ResponseInterface
     * @throws \Throwable in case the line could not be parsed
     */
    public function parseResponse(\Pheanstalk\ResponseLine $responseLine, ?string $responseData): ResponseInterface;
}
