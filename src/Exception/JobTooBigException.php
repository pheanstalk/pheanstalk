<?php

declare(strict_types=1);

namespace Pheanstalk\Exception;

/**
 * Indicates that the given job body is larger than the servers configured max-job-size
 */
final class JobTooBigException extends ClientException
{
}
