<?php

namespace Pheanstalk\Command;

use Pheanstalk\Exception;
use Pheanstalk\Response;

/**
 * The 'peek', 'peek-ready', 'peek-delayed' and 'peek-buried' commands.
 *
 * The peek commands let the client inspect a job in the system. There are four
 * variations. All but the first (peek) operate only on the currently used tube.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class PeekCommand
    extends AbstractCommand
    implements \Pheanstalk\ResponseParser
{
    const TYPE_ID = 'id';
    const TYPE_READY = 'ready';
    const TYPE_DELAYED = 'delayed';
    const TYPE_BURIED = 'buried';

    private $_subcommands = array(
        self::TYPE_READY,
        self::TYPE_DELAYED,
        self::TYPE_BURIED,
    );

    private $_subcommand;
    private $_jobId;

    /**
     * @param mixed $peekSubject Job ID or self::TYPE_*
     */
    public function __construct($peekSubject)
    {
        if (is_int($peekSubject) || ctype_digit($peekSubject)) {
            $this->_jobId = $peekSubject;
        } elseif (in_array($peekSubject, $this->_subcommands)) {
            $this->_subcommand = $peekSubject;
        } else {
            throw new Exception\CommandException(sprintf(
                'Invalid peek subject: %s', $peekSubject
            ));
        }
    }

    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
    public function getCommandLine()
    {
        return isset($this->_jobId) ?
            sprintf('peek %u', $this->_jobId) :
            sprintf('peek-%s', $this->_subcommand);
    }

    /* (non-phpdoc)
     * @see ResponseParser::parseResponse()
     */
    public function parseResponse($responseLine, $responseData)
    {
        if ($responseLine == Response::RESPONSE_NOT_FOUND) {
            if (isset($this->_jobId)) {
                $message = sprintf(
                    '%s: Job %u does not exist.',
                    $responseLine,
                    $this->_jobId
                );
            } else {
                $message = sprintf(
                    "%s: There are no jobs in the '%s' status",
                    $responseLine,
                    $this->_subcommand
                );
            }

            throw new Exception\ServerException($message);
        } elseif (preg_match('#^FOUND (\d+) \d+$#', $responseLine, $matches)) {
            return $this->_createResponse(
                Response::RESPONSE_FOUND,
                array(
                    'id' => (int) $matches[1],
                    'jobdata' => $responseData,
                )
            );
        }
    }
}
