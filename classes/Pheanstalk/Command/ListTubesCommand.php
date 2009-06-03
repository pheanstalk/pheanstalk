<?php

/**
 * The 'list-tubes' command
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Command_ListTubesCommand
	extends Pheanstalk_Command_AbstractCommand
{
	/* (non-phpdoc)
	 * @see Pheanstalk_Command::getCommandLine()
	 */
	public function getCommandLine()
	{
		return 'list-tubes';
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_ResponseParser::parseRespose()
	 */
	public function parseResponse($responseLine, $responseData)
	{
		if (!preg_match('#^OK \d+$#', $responseLine))
		{
			throw new Pheanstalk_Exception_ServerException(sprintf(
				'Unhandled response: %s',
				$responseLine
			));
		}

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
