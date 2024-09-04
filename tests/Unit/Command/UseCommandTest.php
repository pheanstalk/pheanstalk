<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\UseCommand;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\TubeName;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\UseCommand
 */
final class UseCommandTest extends TubeCommandTestBase
{
    public function testInterpretUsing(): void
    {
        $command = $this->getSubject();
        $using = $command->interpret(new RawResponse(ResponseType::Using, "1ab5"));
        Assert::assertSame("1ab5", $using->value);
    }

    /**
     * @return list<ResponseType>
     */
    protected static function getSupportedResponses(): array
    {
        return [ResponseType::Using];
    }

    protected function getSubject(?TubeName $tube = null): UseCommand
    {
        return new UseCommand($tube ?? new TubeName("default"));
    }
}
