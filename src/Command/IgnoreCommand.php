<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Response\ArrayResponse;

/**
 * The 'ignore' command.
 * Removes a tube from the watch list to reserve jobs from.
 */
class IgnoreCommand extends TubeCommand implements ResponseParserInterface
{
    public function getCommandLine(): string
    {
        return 'ignore '.$this->tube;
    }

    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        if (preg_match('#^WATCHING (\d+)$#', $responseLine, $matches)) {
            return $this->createResponse('WATCHING', [
                'count' => (int) $matches[1],
            ]);
        } elseif ($responseLine == ResponseInterface::RESPONSE_NOT_IGNORED) {
            throw new Exception\ServerException(
                $responseLine.': cannot ignore last tube in watchlist'
            );
        } else {
            throw new Exception('Unhandled response: '.$responseLine);
        }
    }
}
