<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\UseCommand;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\TubeName;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\UseCommand
 */
class UseCommandTest extends TubeCommandTest
{
    public function testInterpretUsing(): void
    {
        $command = $this->getSubject();
        $using = $command->interpret(new RawResponse(ResponseType::Using, "1ab5"));
        Assert::assertSame("1ab5", $using->value);
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::Using];
    }

    protected function getSubject(TubeName $tube = null): UseCommand
    {
        return new UseCommand($tube ?? new TubeName("default"));
    }
}
