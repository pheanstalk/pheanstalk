<?php

/**
 * The 'list-tubes-watched' command.
 * Lists the tubes on the watchlist.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Command_ListTubesWatchedCommand
	extends Pheanstalk_Command_AbstractCommand
{
	/* (non-phpdoc)
	 * @see Pheanstalk_Command::getCommandLine()
	 */
	public function getCommandLine()
	{
		return 'list-tubes-watched';
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_ResponseParser::parseRespose()
	 */
	public function parseResponse($responseLine, $responseData)
	{
		$dataLines = explode("\n", rtrim($responseData));
		array_shift($dataLines); // discard header line

		return $this->_createResponse('OK', array(
			'tubes' => array_map(array($this, '_mapResponseListLines'), $dataLines),
		));
	}

	private function _mapResponseListLines($line)
	{
		return ltrim($line, "- ");
	}
}
