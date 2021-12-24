<?php
declare(strict_types=1);

namespace Pheanstalk\Parser;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\ResponseType;

/**
 * A parser that uses the command's parser to parse a response
 */
class CommandParser implements ResponseParserInterface
{

    public function parseResponse(
        CommandInterface $command,
        ResponseType $type,
        array $arguments = [],
        ?string $data = null
    ): null|ResponseInterface {
        return $command->getResponseParser()->parseResponse($command, $type, $arguments, $data);
    }
}
