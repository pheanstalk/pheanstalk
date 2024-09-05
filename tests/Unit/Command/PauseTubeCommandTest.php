<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\PauseTubeCommand;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\TubeName;

/**
 * @covers \Pheanstalk\Command\PauseTubeCommand
 */
final class PauseTubeCommandTest extends TubeCommandTestBase
{
    public function testInterpretPaused(): void
    {
        $command = $this->getSubject();
        $command->interpret(new RawResponse(ResponseType::Paused));
        $this->expectNotToPerformAssertions();
    }

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Paused];
    }

    protected function getSubject(?TubeName $tube = null): PauseTubeCommand
    {
        return new PauseTubeCommand($tube ?? new TubeName("default"), 123);
    }
}
