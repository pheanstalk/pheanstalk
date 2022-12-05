<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Values;

use Pheanstalk\Values\Timeout;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Pheanstalk\Values\Timeout
 */
class TimeoutTest extends TestCase
{
    public function testToFloat(): void
    {
        self::assertSame(1.5, (new Timeout(1, 500000))->toFloat());
    }

    public function testToArray(): void
    {
        self::assertSame([
            'sec' => 1,
            'usec' => 500000
        ], (new Timeout(1, 500000))->toArray());
    }
}
