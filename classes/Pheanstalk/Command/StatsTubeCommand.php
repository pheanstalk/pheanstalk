<?php

namespace Pheanstalk\Command;
use Pheanstalk\YamlResponseParser;

/**
 * The 'stats-tube' command.
 * Gives statistical information about the specified tube if it exists.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class StatsTubeCommand extends AbstractCommand
{
	private $_tube;

	/**
	 * @param string $tube
	 */
	public function __construct($tube)
	{
		$this->_tube = $tube;
	}

	/* (non-phpdoc)
	 * @see \Pheanstalk\ICommand::getCommandLine()
	 */
	public function getCommandLine()
	{
		return sprintf('stats-tube %s', $this->_tube);
	}

	/* (non-phpdoc)
	 * @see \Pheanstalk\ICommand::getResponseParser()
	 */
	public function getResponseParser()
	{
		return new YamlResponseParser(
			YamlResponseParser::MODE_DICT
		);
	}
}
