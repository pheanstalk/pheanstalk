<?php

namespace Pheanstalk\Command;
use Pheanstalk\IResponseParser;
use Pheanstalk\IResponse;

use Pheanstalk\Exception\ServerException;

/**
 * The 'pause-tube' command.
 * Temporarily prevent jobs being reserved from the given tube.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class PauseTubeCommand extends AbstractCommand implements IResponseParser
{
	private $_tube;
	private $_delay;

	/**
	 * @param string $tube The tube to pause
	 * @param int $delay Seconds before jobs may be reserved from this queue.
	 */
	public function __construct($tube, $delay)
	{
		$this->_tube = $tube;
		$this->_delay = $delay;
	}

	/* (non-phpdoc)
	 * @see \Pheanstalk\ICommand::getCommandLine()
	 */
	public function getCommandLine()
	{
		return sprintf(
			'pause-tube %s %d',
			$this->_tube,
			$this->_delay
		);
	}

	/* (non-phpdoc)
	 * @see \Pheanstalk\IResponseParser::parseRespose()
	 */
	public function parseResponse($responseLine, $responseData)
	{
		if ($responseLine == IResponse::RESPONSE_NOT_FOUND)
		{
			throw new ServerException(sprintf(
				'%s: tube %d does not exist.',
				$responseLine,
				$this->_tube
			));
		}
		elseif ($responseLine == IResponse::RESPONSE_PAUSED)
		{
			return $this->_createResponse(IResponse::RESPONSE_PAUSED);
		}
		else
		{
			throw new \Pheanstalk\Exception('Unhandled response: '.$responseLine);
		}
	}
}
