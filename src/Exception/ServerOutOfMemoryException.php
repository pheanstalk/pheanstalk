<?php

declare(strict_types=1);

namespace Pheanstalk\Exception;

/**
 * An exception originating as a beanstalkd server error.
 */
final class ServerOutOfMemoryException extends ServerException
{
}
