<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\IgnoreCommand;
use Pheanstalk\Exception\NotIgnoredException;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\TubeName;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IgnoreCommand::class)]
final class IgnoreCommandTest extends TubeCommandTestBase
{
    public function testInterpretWatching(): void
    {
        $command = $this->getSubject();
        $watching = $command->interpret(new RawResponse(ResponseType::Watching, "5"));
        Assert::assertSame(5, $watching);
    }

    public function testInterpretNotIgnored(): void
    {
        $command = $this->getSubject();
        $this->expectException(NotIgnoredException::class);
        $command->interpret(new RawResponse(ResponseType::NotIgnored));
    }

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::NotIgnored, ResponseType::Watching];
    }

    protected function getSubject(?TubeName $tube = null): IgnoreCommand
    {
        return new IgnoreCommand($tube ?? new TubeName("default"));
    }
}
