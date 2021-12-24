<?php
declare(strict_types=1);

namespace Pheanstalk\Parser;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\ResponseType;
use SplObjectStorage;

/**
 * Simple parser that tries parsers by locating them in a map, supports falling back to a default parser
 */
class MappedParser implements ResponseParserInterface
{
    /**
     * @phpstan-param SplObjectStorage<ResponseType, ResponseParserInterface> $map
     */
    public function __construct(private readonly SplObjectStorage $map, private readonly ResponseParserInterface|null $default = null) {

    }

    public function parseResponse(
        CommandInterface $command,
        ResponseType $type,
        array $arguments = [],
        ?string $data = null
    ): null|ResponseInterface {
        if (isset($this->map[$type])) {
            if (!$this->map[$type] instanceof ResponseParserInterface) {
                throw new \RuntimeException("Parser map contains non-parser object for type {$type->name}");
            }
            return $this->map[$type]->parseResponse($command, $type, $arguments, $data);
        }
        return isset($this->default) ? $this->default->parseResponse($command, $type, $arguments, $data) : null;
    }
}
