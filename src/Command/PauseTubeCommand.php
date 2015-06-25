<?php

namespace Pheanstalk\Command;

use Pheanstalk\Exception;
use Pheanstalk\Response;

/**
 * The 'pause-tube' command.
 * Temporarily prevent jobs being reserved from the given tube.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class PauseTubeCommand
    extends AbstractCommand
    implements \Pheanstalk\ResponseParser
{
    private $_tube;
    private $_delay;

    /**
     * @param string $tube  The tube to pause
     * @param int    $delay Seconds before jobs may be reserved from this queue.
     */
    public function __construct($tube, $delay)
    {
        $this->_tube = $tube;
        $this->_delay = $delay;
    }

    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
    public function getCommandLine()
    {
        return sprintf(
            'pause-tube %s %u',
            $this->_tube,
            $this->_delay
        );
    }

    /* (non-phpdoc)
     * @see ResponseParser::parseResponse()
     */
    public function parseResponse($responseLine, $responseData)
    {
        if ($responseLine == Response::RESPONSE_NOT_FOUND) {
            throw new Exception\ServerException(sprintf(
                '%s: tube %s does not exist.',
                $responseLine,
                $this->_tube
            ));
        } elseif ($responseLine == Response::RESPONSE_PAUSED) {
            return $this->_createResponse(Response::RESPONSE_PAUSED);
        } else {
            throw new Exception('Unhandled response: '.$responseLine);
        }
    }
}
