<?php

declare(strict_types=1);

namespace Pheanstalk\Tests;

use Pheanstalk\Exception\ClientException;
use Pheanstalk\TubeStats;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Pheanstalk\TubeStats
 */
class TubeStatsTest extends TestCase
{
    public function testThatInvalidTypesThrowClientExceptions(): void
    {
        $this->expectException(ClientException::class);
        TubeStats::fromBeanstalkArray([
            'name' => 'test',
            'current-jobs-ready' => 'fifteen'
        ]);
    }

    public function testTubeName(): void
    {
        $stats = TubeStats::fromBeanstalkArray([
            'name' => 'a-$test'
        ]);
        Assert::assertSame('a-$test', $stats->name->value);
    }
}
