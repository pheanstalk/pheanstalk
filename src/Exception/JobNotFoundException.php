<?php

declare(strict_types=1);

namespace Pheanstalk\Exception;

/**
 * Indicates that the given job was not found by the server
 *
 * inherits from ServerException for backwards compatibility
 */
class JobNotFoundException extends ServerException
{
}
