<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Parser;

use Pheanstalk\Parser\YamlListParser;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(YamlListParser::class)]
final class YamlListParserTest extends TestCase
{
    /**
     * @phpstan-return iterable<array{0: string, 1: list<string>}>
     */
    public static function yamlListProvider(): iterable
    {
        yield ["---\n- a\n- b", ['a', 'b']];
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('yamlListProvider')]
    public function testParse(string $rawData, array $expected): void
    {
        $parser = new YamlListParser();
        Assert::assertSame($expected, $parser->parse($rawData));
    }
}
