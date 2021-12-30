<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\TubeName;

/**
 * A command that is executed against a tube
 */
abstract class TubeCommand implements CommandInterface
{
    /**
     * @return string A template for generating the command
     */
    abstract protected function getCommandTemplate(): string;

    public function __construct(protected readonly TubeName $tube)
    {
    }

    final public function getCommandLine(): string
    {
        return strtr($this->getCommandTemplate(), ['{tube}' => $this->tube->value]);
    }
}
