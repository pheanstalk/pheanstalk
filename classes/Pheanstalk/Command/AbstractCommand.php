<?php

namespace Pheanstalk\Command;
use Pheanstalk\ICommand;
use Pheanstalk\Exception\CommandException;
use Pheanstalk\Response\ArrayResponse;

/**
 * Common functionality for \Pheanstalk\Command implementations.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
abstract class AbstractCommand implements ICommand
{
	/* (non-phpdoc)
	 * @see \Pheanstalk\ICommand::hasData()
	 */
	public function hasData()
	{
		return false;
	}

	/* (non-phpdoc)
	 * @see \Pheanstalk\ICommand::getData()
	 */
	public function getData()
	{
		throw new CommandException('Command has no data');
	}

	/* (non-phpdoc)
	 * @see \Pheanstalk\ICommand::getDataLength()
	 */
	public function getDataLength()
	{
		throw new CommandException('Command has no data');
	}

	/* (non-phpdoc)
	 * @see \Pheanstalk\ICommand::getResponseParser()
	 */
	public function getResponseParser()
	{
		// concrete implementation must either:
		// a) implement \Pheanstalk\IResponseParser
		// b) override this getResponseParser method
		return $this;
	}

	/**
	 * The string representation of the object.
	 * @return string
	 */
	public function __toString()
	{
		return $this->getCommandLine();
	}

	// ----------------------------------------
	// protected

	/**
	 * Creates a \Pheanstalk\IResponse for the given data
	 * @param array
	 * @return object \Pheanstalk\Response\ArrayResponse
	 */
	protected function _createResponse($name, $data = array())
	{
		return new ArrayResponse($name, $data);
	}
}
