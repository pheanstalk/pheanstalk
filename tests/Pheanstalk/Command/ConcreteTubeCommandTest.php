<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\TubeCommand;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\RawResponse;
use Pheanstalk\TubeName;

/**
 * @covers \Pheanstalk\Command\TubeCommand
 */
class ConcreteTubeCommandTest extends TubeCommandTest
{
    protected function getSupportedResponses(): array
    {
        return [];
    }

    protected function getSubject(TubeName $tube = null): TubeCommand
    {
        return new class($tube ?? new TubeName('default')) extends TubeCommand {
            public function interpret(
                RawResponse $response
            ): never {
                throw new UnsupportedResponseException($response->type);
            }

            protected function getCommandTemplate(): string
            {
                return "concrete {tube}";
            }
        };
    }
}
