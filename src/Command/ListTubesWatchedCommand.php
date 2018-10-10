<?php

namespace Pheanstalk\Command;

use Pheanstalk\YamlResponseParser;

/**
 * The 'list-tubes-watched' command.
 *
 * Lists the tubes on the watchlist.
 *
 * @author  Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class ListTubesWatchedCommand
    extends AbstractCommand
{
    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
    public function getCommandLine(): string
    {
        return 'list-tubes-watched';
    }

    /* (non-phpdoc)
     * @see Command::getResponseParser()
     */
    public function getResponseParser(): \Pheanstalk\Contract\ResponseParserInterface
    {
        return new YamlResponseParser(
            YamlResponseParser::MODE_LIST
        );
    }
}
