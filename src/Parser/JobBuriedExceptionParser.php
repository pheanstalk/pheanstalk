<?php
declare(strict_types=1);

namespace Pheanstalk\Parser;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\JobBuriedException;
use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\ResponseType;

/**
 * This parser will throw a JobBuriedException if the response type matches
 */
class JobBuriedExceptionParser implements ResponseParserInterface
{

    public function parseResponse(
        CommandInterface $command,
        ResponseType $type,
        array $arguments = [],
        ?string $data = null
    ): null|ResponseInterface {
        if ($type === ResponseType::BURIED) {
            throw new JobBuriedException();
        }
        return null;
    }
}
