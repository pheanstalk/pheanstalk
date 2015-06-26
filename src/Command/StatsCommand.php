<?php

namespace Pheanstalk\Command;

use Pheanstalk\YamlResponseParser;

/**
 * The 'stats' command.
 * Statistical information about the system as a whole.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class StatsCommand
    extends AbstractCommand
{
    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
    public function getCommandLine()
    {
        return 'stats';
    }

    /* (non-phpdoc)
     * @see Command::getResponseParser()
     */
    public function getResponseParser()
    {
        return new YamlResponseParser(
            YamlResponseParser::MODE_DICT
        );
    }
}
