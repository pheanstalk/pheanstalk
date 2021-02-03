<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Response\ArrayResponse;

/**
 * The 'watch' command.
 * Adds a tube to the watchlist to reserve jobs from.
 */
class WatchCommand extends TubeCommand implements ResponseParserInterface
{
    public function getCommandLine(): string
    {
        return 'watch ' . $this->tube;
    }

    public function parseResponse(\Pheanstalk\ResponseLine $responseLine, ?string $responseData): \Pheanstalk\Contract\ResponseInterface
    {
        return $this->createResponse($responseLine->getName(), [
            'count' => $responseLine->getArguments()[0],
        ]);
    }
}
