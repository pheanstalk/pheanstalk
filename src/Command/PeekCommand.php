<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Exception;

/**
 * The 'peek', 'peek-ready', 'peek-delayed' and 'peek-buried' commands.
 *
 * The peek commands let the client inspect a job in the system. There are four
 * variations. All but the first (peek) operate only on the currently used tube.
 *
 * @author  Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class PeekCommand
    extends AbstractCommand
    implements \Pheanstalk\Contract\ResponseParserInterface
{
    const TYPE_ID = 'id';
    const TYPE_READY = 'ready';
    const TYPE_DELAYED = 'delayed';
    const TYPE_BURIED = 'buried';

    private const SUBCOMMANDS = [
        self::TYPE_READY,
        self::TYPE_DELAYED,
        self::TYPE_BURIED,
    ];

    /**
     * @var string
     */
    private $subcommand;

    /**
     * @param string $peekSubject self::TYPE_*
     */
    public function __construct(string $peekSubject)
    {
        if (in_array($peekSubject, self::SUBCOMMANDS)) {
            $this->subcommand = $peekSubject;
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
        return sprintf('peek-%s', $this->subcommand);
    }

    /* (non-phpdoc)
     * @see ResponseParser::parseResponse()
     */
    public function parseResponse($responseLine, $responseData)
    {
        if ($responseLine == ResponseInterface::RESPONSE_NOT_FOUND) {
            $message = sprintf(
                "%s: There are no jobs in the '%s' status",
                $responseLine,
                $this->subcommand
            );
            throw new Exception\ServerException($message);
        }

        if (preg_match('#^FOUND (\d+) \d+$#', $responseLine, $matches)) {
            return $this->createResponse(
                ResponseInterface::RESPONSE_FOUND,
                array(
                    'id'      => (int) $matches[1],
                    'jobdata' => $responseData,
                )
            );
        }

        throw new Exception\ServerException("Unexpected response");
    }
}
