<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Parser;

use Pheanstalk\Parser\YamlDictionaryParser;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Pheanstalk\Parser\YamlDictionaryParser
 */
final class YamlDictionaryParserTest extends TestCase
{
    /**
     * @phpstan-return iterable<array{0: string, 1: array<string, string|int>}>
     */
    public function yamlDictionaryProvider(): iterable
    {
        yield ["---\n  a: def\n  b: 15", ['a' => 'def', 'b' => 15]];
        yield ["---\n  a: def\n  b: 15", ['a' => 'def', 'b' => 15]];
        yield ["---\n  a:     def\n  b: 15", ['a' => 'def', 'b' => 15]];
        yield ["---\n  a: \"    def\"\n  b: 15", ['a' => '    def', 'b' => 15]];
    }

    /**
     * @dataProvider yamlDictionaryProvider
     * @param array<string, int|string|bool|float> $expected
     */
    public function testParse(string $rawData, array $expected): void
    {
        $parser = new YamlDictionaryParser();
        Assert::assertSame($expected, $parser->parse($rawData));
    }
}
