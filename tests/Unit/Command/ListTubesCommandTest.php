<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\ListTubesCommand;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\TubeName;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListTubesCommand::class)]
final class ListTubesCommandTest extends CommandTestBase
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

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::Ok];
    }

    protected function getSubject(): ListTubesCommand
    {
        return new ListTubesCommand();
    }
}
