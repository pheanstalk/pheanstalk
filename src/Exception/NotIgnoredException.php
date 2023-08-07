<?php

declare(strict_types=1);

namespace Pheanstalk\Exception;

/**
 * Exception thrown when a client tries to ignore the only tube in its watch list
 */
final class NotIgnoredException extends ClientException
{
}
