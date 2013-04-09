<?php

namespace Pheanstalk;

/**
 * A parser for response data sent from the beanstalkd server
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
interface IResponseParser
{
	/**
	 * Parses raw response data into a Pheanstalk\Response object
	 * @param string $responseLine Without trailing CRLF
	 * @param string $responseData (null if no data)
	 * @return object \Pheanstalk\IResponse
	 */
	public function parseResponse($responseLine, $responseData);
}
