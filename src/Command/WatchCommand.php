<?php

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
        return 'watch '.$this->tube;
    }

    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        return $this->createResponse('WATCHING', [
            'count' => preg_replace('#^WATCHING (.+)$#', '$1', $responseLine),
        ]);
    }
}
