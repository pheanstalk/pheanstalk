<?php

declare(strict_types=1);

namespace Pheanstalk\Exception;

/**
 * An exception originating as a beanstalkd server error.
 */
final class ServerBadFormatException extends ServerException
{
    public function __construct(string $commandLine)
    {
        parent::__construct("Bad format for command {$commandLine}");
    }
}
