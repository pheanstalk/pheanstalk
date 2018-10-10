<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Response\ArrayResponse;

/**
 * The 'use' command.
 *
 * The "use" command is for producers. Subsequent put commands will put jobs into
 * the tube specified by this command. If no use command has been issued, jobs
 * will be put into the tube named "default".
 */
class UseCommand extends TubeCommand implements ResponseParserInterface
{
    public function getCommandLine(): string
    {
        return 'use '.$this->tube;
    }

    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        return $this->createResponse('USING', [
            'tube' => preg_replace('#^USING (.+)$#', '$1', $responseLine),
        ]);
    }
}
