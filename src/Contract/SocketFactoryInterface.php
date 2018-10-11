<?php


namespace Pheanstalk\Contract;

interface SocketFactoryInterface
{
    /**
     * This function must return a connected socket that is ready for reading / writing.
     * @return SocketInterface
     */
    public function create(): SocketInterface;
}
