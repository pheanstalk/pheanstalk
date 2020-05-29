<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Response\ArrayResponse;

/**
 * The 'kick-job' command.
 *
 * Kicks a specific buried or delayed job into a 'ready' state.
 *
 * A variant of kick that operates with a single job. If the given job
 * exists and is in a buried or delayed state, it will be moved to the
 * ready queue of the the same tube where it currently belongs.
 *
 */
class KickJobCommand extends JobCommand implements ResponseParserInterface
{
    public function getCommandLine(): string
    {
        return 'kick-job '.$this->jobId;
    }

    /* (non-phpdoc)
     * @see ResponseParser::parseResponse()
     */
    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        if ($responseLine == ResponseInterface::RESPONSE_NOT_FOUND) {
            throw new Exception\JobNotFoundException(sprintf(
                '%s: Job %d does not exist or is not in a kickable state.',
                $responseLine,
                $this->jobId
            ));
        } elseif ($responseLine == ResponseInterface::RESPONSE_KICKED) {
            return $this->createResponse(ResponseInterface::RESPONSE_KICKED);
        } else {
            throw new Exception('Unhandled response: '.$responseLine);
        }
    }
}
