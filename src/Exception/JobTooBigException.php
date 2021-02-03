<?php
declare(strict_types=1);

namespace Pheanstalk\Exception;

/**
 * Indicates that the given job body is larger then the servers configured max-job-size
 */
class JobTooBigException extends ClientException
{
}
