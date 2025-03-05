<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\KickCommand;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(KickCommand::class)]
final class KickCommandTest extends CommandTestBase
{
    public function testInterpretKicked(): void
    {
        $result = $this->getSubject()->interpret(new RawResponse(ResponseType::Kicked, "15"));
        Assert::assertSame(15, $result);
    }

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::Kicked];
    }

    protected function getSubject(): KickCommand
    {
        return new KickCommand(123);
    }
}
