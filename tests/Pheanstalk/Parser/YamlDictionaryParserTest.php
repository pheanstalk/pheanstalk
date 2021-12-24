<?php
declare(strict_types=1);

namespace Pheanstalk\Tests\Parser;

use Pheanstalk\Command\ListTubesCommand;
use Pheanstalk\Command\StatsTubeCommand;
use Pheanstalk\Parser\YamlDictionaryParser;
use Pheanstalk\Parser\YamlListParser;
use Pheanstalk\ResponseType;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Pheanstalk\Parser\YamlDictionaryParser
 */
final class YamlDictionaryParserTest extends TestCase
{
    public function yamlDictionaryProvider(): iterable
    {
        yield ["---\n  a: def\n  b: 15", ['a' => 'def', 'b' => "15"]];

    }

    /**
     * @dataProvider yamlDictionaryProvider
     */
    public function testParse(string $rawData, array $expected): void
    {
        $parser = new YamlDictionaryParser();
        $response = $parser->parseResponse(new ListTubesCommand(), ResponseType::OK, [], $rawData);
        Assert::assertSame($expected, (array)$response);
    }
}
