<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\WatchCommand;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\TubeName;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WatchCommand::class)]
final class WatchCommandTest extends TubeCommandTestBase
{
    public function testInterpretWatching(): void
    {
        $command = $this->getSubject();
        $watching = $command->interpret(new RawResponse(ResponseType::Watching, "5"));
        Assert::assertSame(5, $watching);
    }

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::Watching];
    }

    protected function getSubject(?TubeName $tube = null): WatchCommand
    {
        return new WatchCommand($tube ?? new TubeName("default"));
    }
}
