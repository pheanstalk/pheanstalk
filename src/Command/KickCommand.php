<?php

namespace Pheanstalk\Command;

/**
 * The 'kick' command.
 * Kicks buried or delayed jobs into a 'ready' state.
 * If there are buried jobs, it will kick up to $max of them.
 * Otherwise, it will kick up to $max delayed jobs.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class KickCommand
    extends AbstractCommand
    implements \Pheanstalk\ResponseParser
{
    private $_max;

    /**
     * @param int $max The maximum number of jobs to kick
     */
    public function __construct($max)
    {
        $this->_max = (int) $max;
    }

    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
    public function getCommandLine()
    {
        return 'kick '.$this->_max;
    }

    /* (non-phpdoc)
     * @see ResponseParser::parseResponse()
     */
    public function parseResponse($responseLine, $responseData)
    {
        list($code, $count) = explode(' ', $responseLine);

        return $this->_createResponse($code, array(
            'kicked' => (int) $count,
        ));
    }
}
