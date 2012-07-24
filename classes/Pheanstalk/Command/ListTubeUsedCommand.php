<?php

namespace Pheanstalk\Command;
use Pheanstalk\IResponseParser;

/**
 * The 'list-tube-used' command.
 * Returns the tube currently being used by the client.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class ListTubeUsedCommand extends AbstractCommand implements IResponseParser
{
	/* (non-phpdoc)
	 * @see \Pheanstalk\ICommand::getCommandLine()
	 */
	public function getCommandLine()
	{
		return 'list-tube-used';
	}

	/* (non-phpdoc)
	 * @see \Pheanstalk\IResponseParser::parseRespose()
	 */
	public function parseResponse($responseLine, $responseData)
	{
		return $this->_createResponse('USING', array(
			'tube' => preg_replace('#^USING (.+)$#', '$1', $responseLine)
		));
	}
}
