<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\YamlResponseParserInterface;

/**
 * The 'list-tubes' command.
 *
 * List all existing tubes.
 *
 * @author  Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class ListTubesCommand
    extends AbstractCommand
{
    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
    public function getCommandLine()
    {
        return 'list-tubes';
    }

    /* (non-phpdoc)
     * @see Command::getResponseParser()
     */
    public function getResponseParser()
    {
        return new YamlResponseParserInterface(
            YamlResponseParserInterface::MODE_LIST
        );
    }
}
