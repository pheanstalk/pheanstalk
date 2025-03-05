<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Parser;

use Pheanstalk\Parser\YamlDictionaryParser;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(YamlDictionaryParser::class)]
final class YamlDictionaryParserTest extends TestCase
{
    /**
     * @phpstan-return iterable<array{0: string, 1: array<string, string|int|float|bool>}>
     */
    public static function yamlDictionaryProvider(): iterable
    {
        yield ["---\n  a: def\n  b: 15", ['a' => 'def', 'b' => 15]];
        yield ["---\n  a: def\n  b: 15", ['a' => 'def', 'b' => 15]];
        yield ["---\n  a:     def\n  b: 15", ['a' => 'def', 'b' => 15]];
        yield ["---\n  a: \"    def\"\n  b: 15", ['a' => '    def', 'b' => 15]];

        // Special cases, some keys are not quoted, but will be in the future
        //  https://github.com/beanstalkd/beanstalkd/issues/610
        yield ["---\n  os: test123\n  b: 15", ['os' => 'test123', 'b' => 15]];
        yield ["---\n  os: \"test123\"\n  b: 15", ['os' => 'test123', 'b' => 15]];


        // Floats
        yield ["---\n  a: \"    def\"\n  b: 1.5", ['a' => '    def', 'b' => 1.5]];

        // Booleans
        yield ["---\n  a: true\n  b: false", ['a' => true, 'b' => false]];
    }

    /**
     * @param array<string, int|string|bool|float> $expected
     */
    #[DataProvider('yamlDictionaryProvider')]
    public function testParse(string $rawData, array $expected): void
    {
        $parser = new YamlDictionaryParser();
        Assert::assertSame($expected, $parser->parse($rawData));
    }
}
