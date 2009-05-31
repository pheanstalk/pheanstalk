<?php

/**
 * The 'reserve' command.
 * Reserves/locks a ready job in a watched tube.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Command_ReserveCommand
	extends Pheanstalk_Command_AbstractCommand
{
	/* (non-phpdoc)
	 * @see Pheanstalk_Command::getCommandLine()
	 */
	public function getCommandLine()
	{
		return 'reserve';
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_ResponseParser::parseRespose()
	 */
	public function parseResponse($responseLine, $responseData)
	{
		if ($responseLine === Pheanstalk_Response::RESPONSE_DEADLINE_SOON)
		{
			return $this->_createResponse($responseLine);
		}

		list($code, $id) = explode(' ', $responseLine);

		return $this->_createResponse($code, array(
			'id' => (int)$id,
			'jobdata' => $responseData,
		));
	}
}
