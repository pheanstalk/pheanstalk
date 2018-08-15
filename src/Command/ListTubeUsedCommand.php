<?php

namespace Pheanstalk\Command;

/**
 * The 'list-tube-used' command.
 *
 * Returns the tube currently being used by the client.
 *
 * @author  Paul Annesley
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class ListTubeUsedCommand extends AbstractCommand implements \Pheanstalk\ResponseParser
{
    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
    public function getCommandLine()
    {
        return 'list-tube-used';
    }

    /* (non-phpdoc)
     * @see ResponseParser::parseResponse()
     */
    public function parseResponse($responseLine, $responseData)
    {
        return $this->_createResponse('USING', [
            'tube' => preg_replace('#^USING (.+)$#', '$1', $responseLine),
        ]);
    }
}
