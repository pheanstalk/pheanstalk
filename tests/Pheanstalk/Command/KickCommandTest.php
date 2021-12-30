<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\KickCommand;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\KickCommand
 */
class KickCommandTest extends CommandTest
{
    public function testInterpretKicked(): void
    {
        $result = $this->getSubject()->interpret(new RawResponse(ResponseType::Kicked, "15"));
        Assert::assertSame(15, $result);
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::Kicked];
    }

    protected function getSubject(): KickCommand
    {
        return new KickCommand(123);
    }
}
