<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\TubeCommand;
use Pheanstalk\Exception\TubeNotFoundException;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\TubeCommandTemplate;
use Pheanstalk\Values\TubeName;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TubeCommand::class)]
final class ConcreteTubeCommandTest extends TubeCommandTestBase
{
    protected static function getSupportedResponses(): array
    {
        return [
            ResponseType::NotFound
        ];
    }

    protected function getSubject(?TubeName $tube = null): TubeCommand
    {
        /** @psalm-suppress InternalClass */
        return new class($tube ?? new TubeName('default')) extends TubeCommand {
            public function interpret(
                RawResponse $response
            ): never {
                throw match ($response->type) {
                    ResponseType::NotFound => new TubeNotFoundException(),
                    default => new UnsupportedResponseException($response->type)
                };
            }

            protected function getCommandTemplate(): TubeCommandTemplate
            {
                return new TubeCommandTemplate("concrete {tube}");
            }
        };
    }
}
