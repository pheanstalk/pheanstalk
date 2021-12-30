<?php

declare(strict_types=1);

namespace Pheanstalk\Contract;

/**
 * A command to be sent to the beanstalkd server, and response processing logic.
 */
interface CommandWithDataInterface extends CommandInterface
{
    /**
     * The binary data to follow the command.
     */
    public function getData(): string;
}
