<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\StatsJobCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\StatsJobCommand
 */
final class StatsJobCommandTest extends JobCommandTestBase
{
    public function testInterpretOk(): void
    {
        $command = $this->getSubject();

        $jobStats = $command->interpret(new RawResponse(
            ResponseType::Ok,
            null,
            <<<YAML
            ---
            id: 5
            tube: default
            state: delayed
            time-left: 51
            pri: 123
            age: 12
            delay: 123
            ttr: 60
            file: 1
            reserves: 0
            timeouts: 11
            releases: 4
            buries: 1
            kicks: 6
        YAML
        ));
        Assert::assertSame("5", $jobStats->id->getId());
    }

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Ok];
    }

    protected function getSubject(JobIdInterface $jobId = null): StatsJobCommand
    {
        return new StatsJobCommand($jobId ?? new JobId(5));
    }
}
