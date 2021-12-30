<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Parser;

use Pheanstalk\Parser\YamlListParser;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Pheanstalk\Parser\YamlListParser
 */
final class YamlListParserTest extends TestCase
{
    /**
     * @phpstan-return iterable<array{0: string, 1: list<string>}>
     */
    public function yamlListProvider(): iterable
    {
        yield ["---\n- a\n- b", ['a', 'b']];
    }

    /**
     * @dataProvider yamlListProvider
     * @param list<string> $expected
     */
    public function testParse(string $rawData, array $expected): void
    {
        $parser = new YamlListParser();
        Assert::assertSame($expected, $parser->parse($rawData));
    }
}
