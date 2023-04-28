<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\ListTubeUsedCommand;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\ListTubeUsedCommand
 */
final class ListTubeUsedCommandTest extends CommandTestBase
{
    public function testInterpretUsing(): void
    {
        Assert::assertSame("using", $this->getSubject()->interpret(new RawResponse(ResponseType::Using, "using"))->value);
    }

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::Using];
    }

    protected function getSubject(): ListTubeUsedCommand
    {
        return new ListTubeUsedCommand();
    }
}
