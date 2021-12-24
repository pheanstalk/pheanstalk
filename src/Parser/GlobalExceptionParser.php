<?php
declare(strict_types=1);

namespace Pheanstalk\Parser;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\ServerBadFormatException;
use Pheanstalk\Exception\ServerInternalErrorException;
use Pheanstalk\Exception\ServerOutOfMemoryException;
use Pheanstalk\Exception\ServerUnknownCommandException;
use Pheanstalk\ResponseType;

class GlobalExceptionParser implements ResponseParserInterface
{

    public function parseResponse(
        CommandInterface $command,
        ResponseType $type,
        array $arguments = [],
        ?string $data = null
    ): null|ResponseInterface {
        return match($type) {
            ResponseType::OUT_OF_MEMORY => throw new ServerOutOfMemoryException(),
            ResponseType::INTERNAL_ERROR => throw new ServerInternalErrorException(),
            ResponseType::BAD_FORMAT => throw new ServerBadFormatException(),
            ResponseType::UNKNOWN_COMMAND => throw new ServerUnknownCommandException(),
            default => null
        };
    }



}
