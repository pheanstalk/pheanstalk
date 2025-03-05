<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\StatsTubeCommand;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use Pheanstalk\Values\TubeName;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(StatsTubeCommand::class)]
final class StatsTubeCommandTest extends TubeCommandTestBase
{
    private const SAMPLE = [
        'name' => 'tubename',
        'current-jobs-urgent' => 1,
        'current-jobs-ready' => 2,
        'current-jobs-reserved' => 3,
        'current-jobs-delayed' => 4,
        'current-jobs-buried' => 5,
        'total-jobs' => 6,
        'current-using' => 7,
        'current-waiting' => 11,
        'current-watching' => 12,
        'pause' => 13,
        'cmd-delete' => 14,
        'cmd-pause-tube' => 15,
        'pause-time-left' => 16
    ];

    /**
     * @param array<string, scalar> $overrides
     * @return string
     */
    private function createYamlSample(array $overrides): string
    {
        $result = '';
        foreach ([...self::SAMPLE, ...$overrides] as $key => $value) {
            $result .= "$key: $value\n";
        }
        return $result;
    }

    public function testInterpretOk(): void
    {
        $command = $this->getSubject();
        $tubeStats = $command->interpret(new RawResponse(
            ResponseType::Ok,
            null,
            $this->createYamlSample(['name' => "tube-123.a(true\$story)"])
        ));
        Assert::assertSame("tube-123.a(true\$story)", $tubeStats->name->value);
    }

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Ok];
    }

    protected function getSubject(?TubeName $tube = null): StatsTubeCommand
    {
        return new StatsTubeCommand($tube ?? new TubeName("default"));
    }
}
