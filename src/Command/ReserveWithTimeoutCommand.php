<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Response\ArrayResponse;

/**
 * The 'reserve' command.
 * Reserves/locks a ready job in a watched tube.
 */
class ReserveWithTimeoutCommand extends AbstractCommand implements ResponseParserInterface
{
    private $timeout;

    /**
     * A timeout value of 0 will cause the server to immediately return either a
     * response or TIMED_OUT.  A positive value of timeout will limit the amount of
     * time the client will block on the reserve request until a job becomes
     * available.
     *
     * @param int $timeout
     */
    public function __construct(int $timeout)
    {
        $this->timeout = $timeout;
    }

    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
    public function getCommandLine(): string
    {
        return sprintf('reserve-with-timeout %s', $this->timeout);
    }

    public function parseResponse(string $responseLine, ?string $responseData): ArrayResponse
    {
        if (in_array($responseLine, [ResponseInterface::RESPONSE_DEADLINE_SOON, ResponseInterface::RESPONSE_TIMED_OUT], true)) {
            return $this->createResponse($responseLine);
        }

        list($code, $id) = explode(' ', $responseLine);

        return $this->createResponse($code, [
            'id'      => (int) $id,
            'jobdata' => $responseData,
        ]);
    }
}
