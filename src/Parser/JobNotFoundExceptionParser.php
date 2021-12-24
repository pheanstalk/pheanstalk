<?php
declare(strict_types=1);

namespace Pheanstalk\Parser;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\ResponseType;

/**
 * This parser will throw a JobNotFoundException if the response type matches
 */
class JobNotFoundExceptionParser implements ResponseParserInterface
{

    public function parseResponse(
        CommandInterface $command,
        ResponseType $type,
        array $arguments = [],
        ?string $data = null
    ): null|ResponseInterface {
        if ($type === ResponseType::NOT_FOUND) {
            throw new JobNotFoundException();
        }
        return null;
    }
}
