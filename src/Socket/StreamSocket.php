<?php


namespace Pheanstalk\Socket;

use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\SocketException;

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

        if (!function_exists('stream_socket_client')) {
            throw new \Exception('Streams extension not found');
        }

        $target = "tcp://$host:$port";
        $context = stream_context_create();
        $this->socket = stream_socket_client($target, $error, $errorMessage, $connectTimeout, null, $context);
        if ($this->socket === false) {
            throw new SocketException($errorMessage, $error);
        }
    }
}
