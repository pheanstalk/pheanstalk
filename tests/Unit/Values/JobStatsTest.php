<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Values;

use Pheanstalk\Values\JobStats;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Pheanstalk\Values\JobStats
 */
class JobStatsTest extends TestCase
{
    private const SAMPLE = [
        'id' => 123,
        'tube' => 'tube',
        'state' => 'reserved',
        'pri' => 154,
        'age' => 12,
        'delay' => 13,
        'ttr' => 15,
        'time-left' => 0,
        'file' => 14,
        'reserves' => 13,
        'timeouts' => 51,
        'releases' => 13,
        'buries' => 1,
        'kicks' => 1
    ];
    public function testThatInvalidTypesThrowClientExceptions(): void
    {
        $data = self::SAMPLE;
        $stats = JobStats::fromBeanstalkArray($data);

        self::assertSame($data['pri'], $stats->priority);
        self::assertSame($data['age'], $stats->age);
        self::assertSame($data['delay'], $stats->delay);
        self::assertSame($data['ttr'], $stats->timeToRelease);
        self::assertSame($data['time-left'], $stats->timeLeft);
        self::assertSame($data['file'], $stats->file);
        self::assertSame($data['reserves'], $stats->reserves);
        self::assertSame($data['timeouts'], $stats->timeouts);
        self::assertSame($data['releases'], $stats->releases);
        self::assertSame($data['buries'], $stats->buries);
        self::assertSame($data['kicks'], $stats->kicks);
    }

    /**
     * @return iterable<array{0: array<string, scalar>}>
     */
    public function sampleWithSingleMissingKeyProvider(): iterable
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
     * @param array<string, scalar> $sample
     */
    public function testMissingKey(array $sample): void
    {
        $this->expectException(\InvalidArgumentException::class);
        JobStats::fromBeanstalkArray($sample);
    }

    public function testInvalidState(): void
    {
        $sample = self::SAMPLE;
        $sample['state'] = 'invalid_state';
        $this->expectException(\InvalidArgumentException::class);
        JobStats::fromBeanstalkArray($sample);
    }
}
