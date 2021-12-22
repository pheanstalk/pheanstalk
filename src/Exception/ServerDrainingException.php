<?php

declare(strict_types=1);

namespace Pheanstalk\Exception;

/**
 * An exception originating as a beanstalkd server error.
 *
 * @author  Paul Annesley
 */
class ServerDrainingException extends ServerException
{
}
