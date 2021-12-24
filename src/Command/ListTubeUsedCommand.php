<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Parser\EmptySuccessParser;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseType;

/**
 * The 'list-tube-used' command.
 *
 * Returns the tube currently being used by the client.
 */
class ListTubeUsedCommand extends AbstractCommand
{

    public function parseResponse(CommandInterface $command, ResponseType $type, array $arguments = [], null|string $data = null): null|ResponseInterface
    {
        if ($type !== ResponseType::USING) {
            return null;
        }
        return $this->createResponse($type, [
            'tube' => $arguments[0]
        ]);
    }

    public function getType(): CommandType
    {
        return CommandType::LIST_TUBE_USED;
    }

    public function getResponseParser(): ResponseParserInterface
    {
        throw new \RuntimeException('not yet implemented');

    }
}
