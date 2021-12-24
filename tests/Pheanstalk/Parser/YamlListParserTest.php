<?php
declare(strict_types=1);

namespace Pheanstalk\Tests\Parser;

use Pheanstalk\Command\ListTubesCommand;
use Pheanstalk\Command\StatsTubeCommand;
use Pheanstalk\Parser\YamlListParser;
use Pheanstalk\ResponseType;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Pheanstalk\Parser\YamlListParser
 */
final class YamlListParserTest extends TestCase
{
    public function yamlListProvider(): iterable
    {
        yield ["---\n- a\n- b", ['a', 'b']];

    }

    /**
     * @dataProvider yamlListProvider
     */
    public function testParse(string $rawData, array $expected): void
    {
        $parser = new YamlListParser();
        $response = $parser->parseResponse(new ListTubesCommand(), ResponseType::OK, [], $rawData);
        Assert::assertSame($expected, (array)$response);
    }
}
