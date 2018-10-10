<?php

namespace Pheanstalk\Command;

use Pheanstalk\Response\ArrayResponse;

/**
 * The 'watch' command.
 *
 * Adds a tube to the watchlist to reserve jobs from.
 *
 * @author  Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class WatchCommand
    extends AbstractCommand
    implements \Pheanstalk\Contract\ResponseParserInterface
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
    public function getCommandLine(): string
    {
        return 'watch '.$this->_tube;
    }

    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        return $this->createResponse('WATCHING', array(
            'count' => preg_replace('#^WATCHING (.+)$#', '$1', $responseLine),
        ));
    }
}
