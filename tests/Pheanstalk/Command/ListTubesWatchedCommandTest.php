<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\ListTubesWatchedCommand;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\TubeName;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\ListTubesWatchedCommand
 */
final class ListTubesWatchedCommandTest extends CommandTest
{
    public function testInterpretOk(): void
    {
        $result = $this->getSubject()->interpret(new RawResponse(
            ResponseType::Ok,
            null,
            <<<DATA
            - abc
            - ab-cwf
            - \$(a.4)((b
        DATA
        ));
        /** @var TubeName[] $tubeNames */
        $tubeNames = iterator_to_array($result);
        Assert::assertSame('abc', $tubeNames[0]->value);
        Assert::assertSame('ab-cwf', $tubeNames[1]->value);
        Assert::assertSame('$(a.4)((b', $tubeNames[2]->value);
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::Ok];
    }

    protected function getSubject(): ListTubesWatchedCommand
    {
        return new ListTubesWatchedCommand();
    }
}
