<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseLine;

/**
 * The 'peek', 'peek-ready', 'peek-delayed' and 'peek-buried' commands.
 *
 * The peek commands let the client inspect a job in the system. There are four
 * variations. All but the first (peek) operate only on the currently used tube.
 */
class PeekCommand extends AbstractCommand implements ResponseParserInterface
{
    public const TYPE_ID = 'id';
    public const TYPE_READY = 'ready';
    public const TYPE_DELAYED = 'delayed';
    public const TYPE_BURIED = 'buried';

    private const SUBCOMMANDS = [
        self::TYPE_READY,
        self::TYPE_DELAYED,
        self::TYPE_BURIED,
    ];

    private string $subcommand;

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

    public function parseResponse(ResponseLine $responseLine, ?string $responseData): ResponseInterface
    {
        return match($responseLine->getName()) {
            ResponseInterface::RESPONSE_NOT_FOUND => $this->createResponse($responseLine->getName()),
            ResponseInterface::RESPONSE_FOUND => $this->createResponse(
                $responseLine->getName(), ['id' => (int) $responseLine->getArguments()[0], 'jobdata' => $responseData]),
        };
    }
}
