<?php

namespace Pheanstalk\Command;

use Pheanstalk\Exception;
use Pheanstalk\Response;

/**
 * The 'ignore' command.
 * Removes a tube from the watch list to reserve jobs from.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class IgnoreCommand
    extends AbstractCommand
    implements \Pheanstalk\ResponseParser
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
     * @see Command::getCommandLine()
     */
    public function getCommandLine()
    {
        return 'ignore '.$this->_tube;
    }

    /* (non-phpdoc)
     * @see ResponseParser::parseResponse()
     */
    public function parseResponse($responseLine, $responseData)
    {
        if (preg_match('#^WATCHING (\d+)$#', $responseLine, $matches)) {
            return $this->_createResponse('WATCHING', array(
                'count' => (int) $matches[1]
            ));
        } elseif ($responseLine == Response::RESPONSE_NOT_IGNORED) {
            throw new Exception\ServerException($responseLine .
                ': cannot ignore last tube in watchlist');
        } else {
            throw new Exception('Unhandled response: '.$responseLine);
        }
    }
}
