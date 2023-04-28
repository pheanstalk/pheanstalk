<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\StatsCommand;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\StatsCommand
 */
final class StatsCommandTest extends CommandTestBase
{
    private const SAMPLE = [
        'current-jobs-urgent' => 1,
        'current-jobs-ready' => 2,
        'current-jobs-reserved' => 3,
        'current-jobs-delayed' => 4,
        'current-jobs-buried' => 5,

        'cmd-put' => 6,
        'cmd-peek' => 7,
        'cmd-peek-ready' => 8,
        'cmd-peek-delayed' => 9,
        'cmd-peek-buried' => 10,
        'cmd-reserve' => 11,
        'cmd-use' => 12,
        'cmd-watch' => 13,
        'cmd-ignore' => 14,
        'cmd-delete' => 15,
        'cmd-release' => 16,
        'cmd-bury' => 17,
        'cmd-kick' => 18,
        'cmd-stats' => 19,
        'cmd-stats-job' => 20,
        'cmd-stats-tube' => 21,
        'cmd-list-tubes' => 22,
        'cmd-list-tube-used' => 23,
        'cmd-list-tubes-watched' => 24,
        'cmd-reserve-with-timeout' => 391,
        'cmd-pause-tube' => 25,
        'job-timeouts' => 26,
        'total-jobs' => 27,
        'max-job-size' => 28,
        'current-tubes' => 29,
        'current-connections' => 30,
        'current-producers' => 31,
        'current-workers' => 32,
        'current-waiting' => 33,
        'total-connections' => 34,
        'pid' => 35,
        'version' => '"1.2"',
        'rusage-utime' => 0.4,
        'rusage-stime' => 0.5,
        'binlog-oldest-index' => 40,
        'binlog-current-index' => 41,
        'binlog-max-size' => 42,
        'binlog-records-written' => 43,
        'draining' => 'true',
        'id' => 'what',
        'hostname' => 'hostname1',
        'os' => 'debian',
        'platform' => 'linux'
    ];

    /**
     * @param array<string, string|int|float> $overrides
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
        $stats = $this->getSubject()->interpret(new RawResponse(ResponseType::Ok, null, $this->createYamlSample(self::SAMPLE)));
        Assert::assertSame("1.2", $stats->version);
    }

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::Ok];
    }

    protected function getSubject(): StatsCommand
    {
        return new StatsCommand();
    }
}
