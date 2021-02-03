<?php
declare(strict_types=1);

namespace Pheanstalk;


use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Exception\ServerBadFormatException;
use Pheanstalk\Exception\ServerDrainingException;
use Pheanstalk\Exception\ServerInternalErrorException;
use Pheanstalk\Exception\ServerOutOfMemoryException;
use Pheanstalk\Exception\ServerUnknownCommandException;

class ResponseLine
{
    private string $name;
    private array $arguments;
    private int $dataLength = 0;

    private static array $errorResponses = [
        ResponseInterface::RESPONSE_OUT_OF_MEMORY   => ServerOutOfMemoryException::class,
        ResponseInterface::RESPONSE_INTERNAL_ERROR  => ServerInternalErrorException::class,
        ResponseInterface::RESPONSE_DRAINING        => ServerDrainingException::class,
        ResponseInterface::RESPONSE_BAD_FORMAT      => ServerBadFormatException::class,
        ResponseInterface::RESPONSE_UNKNOWN_COMMAND => ServerUnknownCommandException::class,
    ];

    private static array $dataResponses = [
        ResponseInterface::RESPONSE_RESERVED,
        ResponseInterface::RESPONSE_FOUND,
        ResponseInterface::RESPONSE_OK,
    ];

    public function getArguments(): array
    {
        return $this->arguments;
    }
    public function getName(): string
    {
        return $this->name;
    }

    public function __construct(string $name, array $arguments = [])
    {
        if (isset(self::$errorResponses[$name])) {
            throw new self::$errorResponses[$name];
            // Todo:
//            throw new $exceptionClass(sprintf(
//                "%s in response to '%s'",
//                $responseName,
//                $command->getCommandLine()
//            ));

        }
        $this->name = $name;
        if (in_array($name, self::$dataResponses)) {
            $this->dataLength = (int) array_pop($arguments);
        }

        $this->arguments = $arguments;
    }

    public function hasData(): bool
    {
        return $this->dataLength > 0;
    }

    public function getDataLength(): int
    {
        return $this->dataLength;
    }

    public static function fromString(string $line): self
    {
        $parts = preg_split('/\s+/', trim($line));

        $result = new self(array_shift($parts));
        $result->arguments = $parts;

        return $result;
    }
}