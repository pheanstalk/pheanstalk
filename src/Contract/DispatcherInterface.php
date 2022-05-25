<?php

declare(strict_types=1);

namespace Pheanstalk\Contract;

use Pheanstalk\Values\RawResponse;

interface DispatcherInterface
{
    public function dispatch(CommandInterface $command): RawResponse;
}
