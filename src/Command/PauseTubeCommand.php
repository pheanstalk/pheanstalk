<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseLine;

/**
 * The 'pause-tube' command.
 *
 * Temporarily prevent jobs being reserved from the given tube.
 */
class PauseTubeCommand extends TubeCommand implements ResponseParserInterface
{
    /**
     * @var int
     */
    private $delay;

    /**
     * @param string $tube  The tube to pause
     * @param int    $delay Seconds before jobs may be reserved from this queue.
     */
    public function __construct(string $tube, int $delay)
    {
        parent::__construct($tube);
        $this->delay = $delay;
    }

    public function getCommandLine(): string
    {
        return sprintf(
            'pause-tube %s %u',
            $this->tube,
            $this->delay
        );
    }

    public function parseResponse(ResponseLine $responseLine, ?string $responseData): ResponseInterface
    {
        return match ($responseLine->getName()) {
            ResponseInterface::RESPONSE_NOT_FOUND => throw new Exception\ServerException(sprintf(
                '%s: tube %s does not exist.',
                $responseLine->getName(),
                $this->tube
            )),
            ResponseInterface::RESPONSE_PAUSED => $this->createResponse($responseLine->getName())
        };
    }
}
