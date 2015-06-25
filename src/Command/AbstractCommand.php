<?php

namespace Pheanstalk\Command;

use Pheanstalk\Command;
use Pheanstalk\Response;

/**
 * Common functionality for Command implementations.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
abstract class AbstractCommand
    implements Command
{
    /* (non-phpdoc)
     * @see Command::hasData()
     */
    public function hasData()
    {
        return false;
    }

    /* (non-phpdoc)
     * @see Command::getData()
     */
    public function getData()
    {
        throw new Exception\CommandException('Command has no data');
    }

    /* (non-phpdoc)
     * @see Command::getDataLength()
     */
    public function getDataLength()
    {
        throw new Exception\CommandException('Command has no data');
    }

    /* (non-phpdoc)
     * @see Command::getResponseParser()
     */
    public function getResponseParser()
    {
        // concrete implementation must either:
        // a) implement ResponseParser
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
     * Creates a Response for the given data
     * @param array
     * @return object Response
     */
    protected function _createResponse($name, $data = array())
    {
        return new Response\ArrayResponse($name, $data);
    }
}
