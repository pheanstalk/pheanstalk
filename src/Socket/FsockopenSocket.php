<?php

declare(strict_types=1);


namespace Pheanstalk\Socket;

use Pheanstalk\Exception\ConnectionException;

/**
 * A Socket implementation using the fsockopen
 */
class FsockopenSocket extends FileSocket
{
    public function __construct(
        string $host,
        int $port,
        int $connectTimeout
    ) {
        if (!function_exists('fsockopen')) {
            throw new \Exception('Fsockopen not found');
        }

        $socket = @fsockopen($host, $port, $error, $errorMessage, $connectTimeout);
        if ($socket === false) {
            throw new ConnectionException($error, $errorMessage);
        }
        parent::__construct($socket);
    }
}
