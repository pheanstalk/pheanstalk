<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit;

use InvalidArgumentException;
use Pheanstalk\Values\ServerStats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ServerStats::class)]
final class ServerStatsTest extends TestCase
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
        'cmd-touch' => 46,
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
        'version' => "1.2",
        'rusage-utime' => 0.4,
        'rusage-stime' => 0.5,
        'uptime' => 44,
        'binlog-oldest-index' => 40,
        'binlog-current-index' => 41,
        'binlog-max-size' => 42,
        'binlog-records-migrated' => 45,
        'binlog-records-written' => 43,
        'draining' => false,
        'id' => 'what',
        'hostname' => 'hostname1',
        'os' => 'debian',
        'platform' => 'linux'
    ];

    /**
     * @return iterable<array{0: array<string, scalar>}>
     */
    public static function sampleWithSingleMissingKeyProvider(): iterable
    {
        $sample = self::SAMPLE;
        $keys = array_keys($sample);
        for ($i = 0; $i < count($sample); $i++) {
            $copy = $sample;
            unset($copy[$keys[$i]]);
            yield [$copy];
        }
    }

    /**
     * @param array<string, scalar> $sample
     */
    #[DataProvider('sampleWithSingleMissingKeyProvider')]
    public function testMissingKey(array $sample): void
    {
        $this->expectException(InvalidArgumentException::class);
        ServerStats::fromBeanstalkArray($sample);
    }

    public function testHappyPath(): void
    {
        $sample = self::SAMPLE;
        $stats = ServerStats::fromBeanstalkArray($sample);
        self::assertSame($sample['rusage-utime'], $stats->rusageUtime);
    }
}
