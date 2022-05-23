<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\TubeCommand;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\TubeCommandTemplate;
use Pheanstalk\Values\TubeName;

/**
 * @covers \Pheanstalk\Command\TubeCommand
 */
final class ConcreteTubeCommandTest extends TubeCommandTest
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

            protected function getCommandTemplate(): TubeCommandTemplate
            {
                return new TubeCommandTemplate("concrete {tube}");
            }
        };
    }
}
