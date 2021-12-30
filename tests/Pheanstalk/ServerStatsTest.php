<?php

declare(strict_types=1);

namespace Pheanstalk\Tests;

use Pheanstalk\Exception\ClientException;
use Pheanstalk\ServerStats;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Pheanstalk\ServerStats
 */
final class ServerStatsTest extends TestCase
{
    public function testMissingVersion(): void
    {
        $stats = ServerStats::fromBeanstalkArray([
            'a' => 'b'
        ]);
        Assert::assertSame('', $stats->version);
    }

    public function testThatInvalidTypesThrowClientExceptions(): void
    {
        $this->expectException(ClientException::class);
        $stats = ServerStats::fromBeanstalkArray([
            'current-jobs-ready' => 'fifteen'
        ]);
    }
}
