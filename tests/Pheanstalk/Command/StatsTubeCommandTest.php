<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\StatsTubeCommand;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\TubeName;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\StatsTubeCommand
 */
class StatsTubeCommandTest extends TubeCommandTest
{
    public function testInterpretOk(): void
    {
        $command = $this->getSubject();
        $tubeStats = $command->interpret(new RawResponse(
            ResponseType::Ok,
            null,
            <<<DATA
            name: tube-123.a(true\$story)

        DATA
        ));
        Assert::assertSame("tube-123.a(true\$story)", $tubeStats->name->value);
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Ok];
    }

    protected function getSubject(TubeName $tube = null): StatsTubeCommand
    {
        return new StatsTubeCommand($tube ?? new TubeName("default"));
    }
}
