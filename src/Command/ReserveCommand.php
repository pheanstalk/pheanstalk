<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Response\ArrayResponse;

/**
 * The 'reserve' command.
 *
 * Reserves/locks a ready job in a watched tube.
 */
class ReserveCommand extends AbstractCommand implements ResponseParserInterface
{
    public function getCommandLine(): string
    {
        return 'reserve';
    }

    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        if ($responseLine === ResponseInterface::RESPONSE_DEADLINE_SOON) {
            throw new DeadlineSoonException();
        }

        list($code, $id) = explode(' ', $responseLine);
        return $this->createResponse($code, [
            'id'      => (int) $id,
            'jobdata' => $responseData,
        ]);
    }
}
