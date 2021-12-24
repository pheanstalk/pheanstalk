<?php

declare(strict_types=1);


namespace Pheanstalk\Contract;

interface SocketFactoryInterface
{
    public const DEFAULT_PORT = 11300;
    /**
     * This function must return a connected socket that is ready for reading / writing.
     * @return SocketInterface
     */
    public function create(): SocketInterface;
}
