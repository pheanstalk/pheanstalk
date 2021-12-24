<?php
declare(strict_types=1);

namespace Pheanstalk\Parser;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Response\EmptySuccessResponse;
use Pheanstalk\ResponseType;

class EmptySuccessParser implements ResponseParserInterface
{
    public function __construct(private readonly ResponseType $type)
    {
    }

    public function parseResponse(
        CommandInterface $command,
        ResponseType $type,
        array $arguments = [],
        ?string $data = null
    ): null|ResponseInterface {
        if ($type === $this->type) {
            return new EmptySuccessResponse($type);
        }
        return null;
    }
}
