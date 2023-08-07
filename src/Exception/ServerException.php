<?php

declare(strict_types=1);

namespace Pheanstalk\Exception;

use Pheanstalk\Exception;

/**
 * An exception originating as a beanstalkd server error.
 * @internal
 * @extensible
 */
class ServerException extends Exception
{
}
