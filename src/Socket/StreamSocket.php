<?php


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
        $addresses = gethostbynamel($host);
        if ($addresses === false) {
            throw new ConnectionException(0, "Could not resolve hostname $host");
        }
        $target = "tcp://{$addresses[0]}:$port";

        $context = stream_context_create();
        $this->socket = @stream_socket_client($target, $error, $errorMessage, $connectTimeout, STREAM_CLIENT_CONNECT, $context);
        if ($this->socket === false) {
            throw new ConnectionException($errorMessage, $error);
        }
    }
}
