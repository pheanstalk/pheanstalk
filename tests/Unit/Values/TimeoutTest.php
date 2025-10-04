<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Values;

use InvalidArgumentException;
use Pheanstalk\Values\Timeout;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Timeout::class)]
final class TimeoutTest extends TestCase
{
    /**
     * @return list<array{0: int, 1: int}|array{0: int}>
     */
    public static function validConstructorArguments(): array
    {
        return [
            [0, ],
            [0, 0],
            [0, 200],
            [100, 0],
            [100, ],
            [100, 200],
        ];
    }

    /**
     * @return list<array{0: int, 1: int}|array{0: int}>
     */
    public static function invalidConstructorArguments(): array
    {
        return [
            [0, -200],
            [-100, ],
            [-100, 0],
            [-100, -200],
        ];
    }

    #[DataProvider('validConstructorArguments')]
    public function testConstructorWitValidArgumentsCreatesInstance(int $seconds, int $microSeconds = 0): void
    {
        $timeout = new Timeout($seconds, $microSeconds);

        self::assertSame($seconds, $timeout->seconds);
        self::assertSame($microSeconds, $timeout->microSeconds);
    }

    #[DataProvider('invalidConstructorArguments')]
    public function testConstructorWitInvalidArgumentsThrowsException(int $seconds, int $microSeconds = 0): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Timeout($seconds, $microSeconds);
    }

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
