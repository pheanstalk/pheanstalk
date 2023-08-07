<?php

declare(strict_types=1);


namespace Pheanstalk\Socket;

use Pheanstalk\Exception\ConnectionException;
use Pheanstalk\Values\Timeout;

/**
 * A Socket implementation using the fsockopen
 * @internal
 */
final class FsockopenSocket extends FileSocket
{
    public function __construct(
        string $host,
        int $port,
        Timeout $connectTimeout,
        Timeout $readTimeout
    ) {
        $socket = @fsockopen($host, str_starts_with($host, 'unix://') ? -1 : $port, $error, $errorMessage, $connectTimeout->toFloat());

        if ($socket === false) {
            throw new ConnectionException($error, $errorMessage);
        }

        parent::__construct($socket, $readTimeout);
    }
}
