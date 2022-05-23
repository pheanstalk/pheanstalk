<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Values\TubeCommandTemplate;
use Pheanstalk\Values\TubeName;

/**
 * A command that is executed against a tube
 * @internal
 */
abstract class TubeCommand implements CommandInterface
{
    abstract protected function getCommandTemplate(): TubeCommandTemplate;

    public function __construct(protected readonly TubeName $tube)
    {
    }

    final public function getCommandLine(): string
    {
        return $this->getCommandTemplate()->render($this->tube);
    }
}
