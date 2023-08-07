<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Values;

use Pheanstalk\Values\TubeStats;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Pheanstalk\Values\TubeStats
 */
final class TubeStatsTest extends TestCase
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

    public function testThatInvalidTypesThrowClientExceptions(): void
    {
        $data = self::SAMPLE;
        $stats = TubeStats::fromBeanstalkArray($data);

        self::assertSame($data['name'], $stats->name->value);
        self::assertSame($data['current-jobs-urgent'], $stats->currentJobsUrgent);
        self::assertSame($data['current-jobs-ready'], $stats->currentJobsReady);
        self::assertSame($data['current-jobs-reserved'], $stats->currentJobsReserved);
        self::assertSame($data['current-jobs-delayed'], $stats->currentJobsDelayed);
        self::assertSame($data['current-jobs-buried'], $stats->currentJobsBuried);
        self::assertSame($data['total-jobs'], $stats->totalJobs);
        self::assertSame($data['current-using'], $stats->currentUsing);
        self::assertSame($data['current-waiting'], $stats->currentWaiting);
        self::assertSame($data['current-watching'], $stats->currentWatching);
        self::assertSame($data['pause'], $stats->pause);
        self::assertSame($data['cmd-delete'], $stats->cmdDelete);
        self::assertSame($data['cmd-pause-tube'], $stats->cmdPauseTube);
        self::assertSame($data['pause-time-left'], $stats->pauseTimeLeft);
    }

    public function testTubeName(): void
    {
        /** @psalm-suppress DuplicateArrayKey */
        $stats = TubeStats::fromBeanstalkArray([...self::SAMPLE,
            'name' => 'a-$test'
        ]);
        Assert::assertSame('a-$test', $stats->name->value);
    }

    /**
     * @return iterable<array{0: array<string, string|int>}>
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
     * @dataProvider sampleWithSingleMissingKeyProvider
     * @param array<string, string|int> $sample
     */
    public function testMissingKey(array $sample): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TubeStats::fromBeanstalkArray($sample);
    }
}
