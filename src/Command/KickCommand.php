<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Response\ArrayResponse;

/**
 * The 'kick' command.
 *
 * Kicks buried or delayed jobs into a 'ready' state.
 * If there are buried jobs, it will kick up to $max of them.
 * Otherwise, it will kick up to $max delayed jobs.
 */
class KickCommand extends AbstractCommand implements ResponseParserInterface
{
    private $max;

    /**
     * @param int $max The maximum number of jobs to kick
     */
    public function __construct(int $max)
    {
        $this->max = $max;
    }

    public function getCommandLine(): string
    {
        return 'kick '.$this->max;
    }

    /* (non-phpdoc)
     * @see ResponseParser::parseResponse()
     */
    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        list($code, $count) = explode(' ', $responseLine);

        return $this->createResponse($code, [
            'kicked' => (int) $count,
        ]);
    }
}
