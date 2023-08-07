<?php

declare(strict_types=1);

namespace Pheanstalk\Exception;

/**
 * Indicates that the given job was not found by the server
 */
final class JobNotFoundException extends ClientException
{
}
