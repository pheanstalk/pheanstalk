<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\TubeName;

/**
 * A command that is executed against a tube
 */
abstract class TubeCommand extends AbstractCommand
{
    public function __construct(protected readonly TubeName $tube)
    {
    }
    public function getCommandLine(): string
    {
        return "{$this->getType()->value} {$this->tube}";
    }
}
