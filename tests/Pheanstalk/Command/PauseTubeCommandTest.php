<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\PauseTubeCommand;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\Success;
use Pheanstalk\TubeName;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\PauseTubeCommand
 */
class PauseTubeCommandTest extends TubeCommandTest
{
    public function testInterpretPaused(): void
    {
        $command = $this->getSubject();
        $command->interpret(new RawResponse(ResponseType::Paused));
        $this->expectNotToPerformAssertions();
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Paused];
    }

    protected function getSubject(TubeName $tube = null): PauseTubeCommand
    {
        return new PauseTubeCommand($tube ?? new TubeName("default"), 123);
    }
}
