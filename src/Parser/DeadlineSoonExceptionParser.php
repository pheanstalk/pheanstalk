<?php
declare(strict_types=1);

namespace Pheanstalk\Parser;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\ResponseType;

/**
 * This parser will throw a DeadlineSoonExceptionParser if the response type matches
 */
class DeadlineSoonExceptionParser implements ResponseParserInterface
{
    public function parseResponse(
        CommandInterface $command,
        ResponseType $type,
        array $arguments = [],
        ?string $data = null
    ): null|ResponseInterface {
        if ($type === ResponseType::DEADLINE_SOON) {
            throw new DeadlineSoonException();
        }
        return null;
    }
}
