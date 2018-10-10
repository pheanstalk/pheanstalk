<?php

namespace Pheanstalk\Command;

use Pheanstalk\Response\ArrayResponse;

/**
 * The 'list-tube-used' command.
 *
 * Returns the tube currently being used by the client.
 *
 * @author  Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class ListTubeUsedCommand
    extends AbstractCommand
    implements \Pheanstalk\Contract\ResponseParserInterface
{
    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
    public function getCommandLine(): string
    {
        return 'list-tube-used';
    }

    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        return $this->createResponse('USING', array(
            'tube' => preg_replace('#^USING (.+)$#', '$1', $responseLine),
        ));
    }
}
