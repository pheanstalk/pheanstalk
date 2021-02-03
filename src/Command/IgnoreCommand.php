<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseLine;

/**
 * The 'ignore' command.
 * Removes a tube from the watch list to reserve jobs from.
 */
class IgnoreCommand extends TubeCommand implements ResponseParserInterface
{
    public function getCommandLine(): string
    {
        return 'ignore ' . $this->tube;
    }

    public function parseResponse(ResponseLine $responseLine, ?string $responseData): ResponseInterface
    {
        if ($responseLine->getName() === ResponseInterface::RESPONSE_WATCHING) {
            return $this->createResponse($responseLine->getName(), ['count' => (int)$responseLine->getArguments()[0]]);
        } elseif ($responseLine->getName() === ResponseInterface::RESPONSE_NOT_IGNORED) {
            throw new Exception\ServerException('Cannot ignore last tube in watchlist');
        } else {
            throw new Exception("Unhandled response: {$responseLine->getName()}");
        }
    }
}
