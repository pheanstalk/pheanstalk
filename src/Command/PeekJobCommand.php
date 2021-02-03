<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Response\ArrayResponse;

/**
 * The 'peek' command.
 *
 * The peek command let the client inspect a specific job in the system.
 */
class PeekJobCommand extends JobCommand implements ResponseParserInterface
{
    public function getCommandLine(): string
    {
        return sprintf('peek %u', $this->jobId);
    }

    public function parseResponse(\Pheanstalk\ResponseLine $responseLine, ?string $responseData): \Pheanstalk\Contract\ResponseInterface
    {
        if ($responseLine == ResponseInterface::RESPONSE_NOT_FOUND) {
            $message = sprintf(
                '%s: Job %u does not exist.',
                $responseLine,
                $this->jobId
            );
            throw new Exception\JobNotFoundException($message);
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

        throw new Exception\ServerException("Unexpected response: " . $responseLine);
    }
}
