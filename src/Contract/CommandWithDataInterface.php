<?php

declare(strict_types=1);

namespace Pheanstalk\Contract;

/**
 * Some commands carry data besides their command line. Commands carrying data implement this interface so that it can
 * be detected and sent by the dispatching code in Connection
 */
interface CommandWithDataInterface extends CommandInterface
{
    /**
     * The binary data to follow the command.
     */
    public function getData(): string;
}
