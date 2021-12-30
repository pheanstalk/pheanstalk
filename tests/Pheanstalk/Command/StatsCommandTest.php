<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\StatsCommand;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\StatsCommand
 */
final class StatsCommandTest extends CommandTest
{
    public function testInterpretOk(): void
    {
        $data = <<<DATA
            - version: "1.2"
        DATA;

        $stats = $this->getSubject()->interpret(new RawResponse(ResponseType::Ok, null, $data));
        Assert::assertSame("1.2", $stats->version);
    }

    public function testInterpretMissingVersion(): void
    {
        $data = <<<DATA
            - someProperty: "1.2"
        DATA;
        $stats = $this->getSubject()->interpret(new RawResponse(ResponseType::Ok, null, $data));
        Assert::assertSame("", $stats->version);
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::Ok];
    }

    protected function getSubject(): StatsCommand
    {
        return new StatsCommand();
    }
}
