<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Response\ArrayResponse;

/**
 * The 'reserve' command.
 *
 * Reserves/locks a ready job in a watched tube.
 *
 * @author  Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class ReserveCommand
    extends AbstractCommand
    implements \Pheanstalk\Contract\ResponseParserInterface
{
    public function __construct()
    {
        if (!empty(func_get_args())) {
            throw new \Exception('In version 4 calling reserve with a parameter is no longer supported.');
        }
    }


    /* (non-phpdoc)
     * @see Command::getCommandLine()
     */
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
