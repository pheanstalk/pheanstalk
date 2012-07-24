<?php

namespace Pheanstalk\Command;
use Pheanstalk\YamlResponseParser;

/**
 * The 'list-tubes' command.
 * List all existing tubes.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class ListTubesCommand extends AbstractCommand
{
	/* (non-phpdoc)
	 * @see \Pheanstalk\ICommand::getCommandLine()
	 */
	public function getCommandLine()
	{
		return 'list-tubes';
	}

	/* (non-phpdoc)
	 * @see \Pheanstalk\ICommand::getResponseParser()
	 */
	public function getResponseParser()
	{
		return new YamlResponseParser(
			YamlResponseParser::MODE_LIST
		);
	}
}
