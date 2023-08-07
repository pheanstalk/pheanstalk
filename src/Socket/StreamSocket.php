<?php

declare(strict_types=1);


namespace Pheanstalk\Socket;

use Pheanstalk\Exception\ConnectionException;
use Pheanstalk\Values\Timeout;

/**
 * A Socket implementation using the Streams extension
 * @internal
 */
final class StreamSocket extends FileSocket
{
    public function __construct(
        string $host,
        int $port,
        Timeout $connectTimeout,
        Timeout $receiveTimeout
    ) {
        $context = stream_context_create();
        if (str_starts_with($host, 'unix://')) {
            $socket = @stream_socket_client($host, $error, $errorMessage, $connectTimeout->toFloat(), STREAM_CLIENT_CONNECT, $context);
        } else {
            $socket = @stream_socket_client(
                "tcp://$host:$port",
                $error,
                $errorMessage,
                $connectTimeout->toFloat(),
                STREAM_CLIENT_CONNECT,
                $context
            );
        }
        if ($socket === false) {
            throw new ConnectionException($error, $errorMessage);
        }
        parent::__construct($socket, $receiveTimeout);
    }
}
