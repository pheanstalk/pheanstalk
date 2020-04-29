<?php

namespace Pheanstalk\Contract;

use Pheanstalk\Response\ArrayResponse;

/**
 * A parser for response data sent from the beanstalkd server.
 *
 * @author  Paul Annesley
 */
interface ResponseParserInterface
{
    /**
     * Parses raw response data into a Response object.
     *
     * @param string $responseLine Without trailing CRLF
     * @param string $responseData (null if no data)
     * @return ArrayResponse
     */
    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse;
}
