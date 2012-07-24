<?php

namespace Pheanstalk\Command;
use Pheanstalk\YamlResponseParser;

/**
 * The 'list-tubes-watched' command.
 * Lists the tubes on the watchlist.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class ListTubesWatchedCommand extends AbstractCommand
{
	/* (non-phpdoc)
	 * @see \Pheanstalk\ICommand::getCommandLine()
	 */
	public function getCommandLine()
	{
		return 'list-tubes-watched';
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
