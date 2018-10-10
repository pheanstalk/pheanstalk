<?php

namespace Pheanstalk\Command;

use Pheanstalk\YamlResponseParser;

/**
 * The 'stats-tube' command.
 *
 * Gives statistical information about the specified tube if it exists.
 *
 * @author  Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class StatsTubeCommand
    extends AbstractCommand
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
        return sprintf('stats-tube %s', $this->_tube);
    }

    /* (non-phpdoc)
     * @see Command::getResponseParser()
     */
    public function getResponseParser(): \Pheanstalk\Contract\ResponseParserInterface
    {
        return new YamlResponseParser(
            YamlResponseParser::MODE_DICT
        );
    }
}
