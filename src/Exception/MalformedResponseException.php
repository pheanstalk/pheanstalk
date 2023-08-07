<?php

declare(strict_types=1);

namespace Pheanstalk\Exception;

/**
 * An exception indicating that the beanstalkd server sent a response in an incorrect or unsupported format
 */
final class MalformedResponseException extends ClientException
{
    public static function expectedDataAndIntegerArgument(): self
    {
        return new self("Expected the response to contain data and an integer argument");
    }

    public static function negativeDataLength(): self
    {
        return new self("Data length cannot be negative");
    }

    public static function expectedData(): self
    {
        return new self("Expected the response to contain data");
    }

    public static function expectedIntegerArgument(): self
    {
        return new self("Argument should be of type integer");
    }

    public static function expectedStringArgument(): self
    {
        return new self("Argument should be of type string");
    }

    public static function expectedArgument(): self
    {
        return new self("Argument the response to contain an argument");
    }
}
