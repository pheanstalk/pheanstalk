<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Response\ArrayResponse;

/**
 * The 'peek', 'peek-ready', 'peek-delayed' and 'peek-buried' commands.
 *
 * The peek commands let the client inspect a job in the system. There are four
 * variations. All but the first (peek) operate only on the currently used tube.
 */
class PeekCommand extends AbstractCommand implements ResponseParserInterface
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

    public function __construct(string $peekSubject)
    {
        if (in_array($peekSubject, self::SUBCOMMANDS)) {
            $this->subcommand = $peekSubject;
        } else {
            throw new Exception\CommandException(sprintf(
                'Invalid peek subject: %s',
                $peekSubject
            ));
        }
    }

    public function getCommandLine(): string
    {
        return sprintf('peek-%s', $this->subcommand);
    }

    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        if ($responseLine == ResponseInterface::RESPONSE_NOT_FOUND) {
            return $this->createResponse(ResponseInterface::RESPONSE_NOT_FOUND);
        }

        if (preg_match('#^FOUND (\d+) \d+$#', $responseLine, $matches)) {
            return $this->createResponse(
                ResponseInterface::RESPONSE_FOUND,
                [
                    'id'      => (int) $matches[1],
                    'jobdata' => $responseData,
                ]
            );
        }

        throw new Exception\ServerException("Unexpected response");
    }
}
