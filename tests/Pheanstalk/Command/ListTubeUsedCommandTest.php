<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\ListTubeUsedCommand;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\ListTubeUsedCommand
 */
final class ListTubeUsedCommandTest extends CommandTest
{
    public function testInterpretUsing(): void
    {
        Assert::assertSame("using", $this->getSubject()->interpret(new RawResponse(ResponseType::Using, "using"))->value);
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::Using];
    }

    protected function getSubject(): ListTubeUsedCommand
    {
        return new ListTubeUsedCommand();
    }
}
