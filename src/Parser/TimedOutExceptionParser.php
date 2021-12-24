<?php
declare(strict_types=1);

namespace Pheanstalk\Parser;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\TimedOutException;
use Pheanstalk\ResponseType;

class TimedOutExceptionParser implements ResponseParserInterface
{

    public function parseResponse(
        CommandInterface $command,
        ResponseType $type,
        array $arguments = [],
        ?string $data = null
    ): null|ResponseInterface {
        if ($type === ResponseType::TIMED_OUT) {
            throw new TimedOutException();
        }
        return null;
    }
}
