<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Response\ArrayResponse;

/**
 * The 'list-tube-used' command.
 *
 * Returns the tube currently being used by the client.
 */
class ListTubeUsedCommand extends AbstractCommand implements ResponseParserInterface
{
    public function getCommandLine(): string
    {
        return 'list-tube-used';
    }

    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        return $this->createResponse('USING', [
            'tube' => preg_replace('#^USING (.+)$#', '$1', $responseLine),
        ]);
    }
}
