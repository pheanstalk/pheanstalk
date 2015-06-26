<?php

namespace Pheanstalk;

/**
 * A parser for response data sent from the beanstalkd server
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
interface ResponseParser
{
    /**
     * Parses raw response data into a Response object
     * @param  string $responseLine Without trailing CRLF
     * @param  string $responseData (null if no data)
     * @return object Response
     */
    public function parseResponse($responseLine, $responseData);
}
