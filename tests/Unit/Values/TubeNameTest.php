<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Values;

use InvalidArgumentException;
use Pheanstalk\Values\TubeName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TubeName::class)]
final class TubeNameTest extends TestCase
{
    public function testNameTooLong(): void
    {
        new TubeName(str_repeat("a", 200));
        $this->expectException(InvalidArgumentException::class);
        new TubeName(str_repeat("a", 201));
    }

    public function testStartingWithHyphen(): void
    {
        new TubeName("ab-test");
        $this->expectException(InvalidArgumentException::class);
        new TubeName("-test");
    }

    public function testUtf8(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TubeName("WЁЇrd");
    }

    /**
     * @return iterable<array{0: string|int}>
     */
    public static function nameProvider(): iterable
    {
        return [
            ["abcd"],
            ["a/b/b/c/d"],
            [1123]
        ];
    }

    #[DataProvider('nameProvider')]
    public function testName(int|string $name): void
    {
        $tube = new TubeName($name);
        self::assertSame((string) $name, $tube->value);
        self::assertSame((string) $name, (string) $tube);
    }
}
