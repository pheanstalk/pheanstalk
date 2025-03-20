<?php

declare(strict_types=1);

namespace Pheanstalk\Contract;

interface PheanstalkClientInterface
{
    /**
     * Closes the current connection and releases all associated resources.
     *
     * This method ensures that after the call, no resources are held by the client,
     * allowing garbage collection to safely reclaim memory. If no connection is open,
     * this method does nothing (noop). Future command dispatches will automatically
     * establish a new connection if needed.
     *
     * Note: If the client has any reserved jobs at the time of disconnection, beanstalkd
     * will automatically release them back into the ready queue.
     */
    public function disconnect(): void;
}
