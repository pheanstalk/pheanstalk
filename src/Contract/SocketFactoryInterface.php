<?php

declare(strict_types=1);


namespace Pheanstalk\Contract;

use Pheanstalk\Exception\ConnectionException;

interface SocketFactoryInterface
{
    public const DEFAULT_PORT = 11300;
    /**
     * This function must return a connected socket that is ready for reading / writing.
     * @return SocketInterface
     * @throws ConnectionException when the underlying implementation is not able to create a connection
     */
    public function create(): SocketInterface;
}
