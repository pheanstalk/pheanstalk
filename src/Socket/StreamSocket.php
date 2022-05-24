<?php

declare(strict_types=1);


namespace Pheanstalk\Socket;

use Pheanstalk\Exception\ConnectionException;

/**
 * A Socket implementation using the Streams extension
 */
class StreamSocket extends FileSocket
{
    public function __construct(
        string $host,
        int $port,
        int $connectTimeout
    ) {
        $context = stream_context_create();
        $socket = @stream_socket_client("tcp://$host:$port", $error, $errorMessage, $connectTimeout, STREAM_CLIENT_CONNECT, $context);
        if ($socket === false) {
            throw new ConnectionException($error, $errorMessage);
        }
        parent::__construct($socket);
    }
}
