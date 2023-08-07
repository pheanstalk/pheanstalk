<?php

declare(strict_types=1);

namespace Pheanstalk\Exception;

/**
 * An exception relating to the client connection to the beanstalkd server.
 */
final class ConnectionException extends ClientException
{
    /**
     * @param int    $errno  The connection error code
     * @param string $errstr The connection error message
     */
    public function __construct($errno, $errstr)
    {
        parent::__construct(sprintf('Socket error %d: %s', $errno, $errstr));
    }
}
