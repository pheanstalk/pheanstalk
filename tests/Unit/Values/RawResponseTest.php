<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Values;

use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Pheanstalk\Values\RawResponse
 */
final class RawResponseTest extends TestCase
{
    public function testNumericStringsAreCast(): void
    {
        self::assertSame(123, (new RawResponse(ResponseType::DeadlineSoon, '123'))->argument);
    }

    public function testTheNumberZeroIsCast(): void
    {
        self::assertSame(0, (new RawResponse(ResponseType::Kicked, '0'))->argument);
    }

    public function testNull(): void
    {
        self::assertNull((new RawResponse(ResponseType::DeadlineSoon))->argument);
    }

    public function testString(): void
    {
        self::assertSame("123a", (new RawResponse(ResponseType::DeadlineSoon, '123a'))->argument);
    }
}
