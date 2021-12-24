<?php

declare(strict_types=1);

namespace Pheanstalk\Exception;

/**
 * Indicates that the given job body was buried due to the server being OOM
 */
class JobBuriedException extends ClientException
{
}
