<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Exception;

/**
 * The 'peek' command.
 *
 * The peek command let the client inspect a specific job in the system.
 *
 * @author  Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class PeekJobCommand
    extends AbstractCommand
    implements \Pheanstalk\Contract\ResponseParserInterface
{
    private $jobId;

    public function __construct(JobIdInterface $peekSubject)
    {
        $this->jobId = $peekSubject->getId();
    }

    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
    public function getCommandLine()
    {
        return sprintf('peek %u', $this->jobId);
    }

    /* (non-phpdoc)
     * @see ResponseParser::parseResponse()
     */
    public function parseResponse($responseLine, $responseData)
    {
        if ($responseLine == ResponseInterface::RESPONSE_NOT_FOUND) {
            $message = sprintf(
                '%s: Job %u does not exist.',
                $responseLine,
                $this->jobId
            );
            throw new Exception\ServerException($message);
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
