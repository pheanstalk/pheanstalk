<?php

declare(strict_types=1);

namespace Pheanstalk\Tests;

use Pheanstalk\Exception\ClientException;
use Pheanstalk\ResponseLine;
use Pheanstalk\YamlResponseParser;

class YamlResponseParserTest extends BaseTestCase
{

    public function testList()
    {
        $parser = new YamlResponseParser(YamlResponseParser::MODE_LIST);
        $response = $parser->parseResponse(ResponseLine::fromString('OK 1'), "---\n- a\n- b");
        $this->assertSame(['a', 'b'], iterator_to_array($response));
    }

    public function testInvalidList()
    {
        $this->expectException(ClientException::class);
        $parser = new YamlResponseParser(YamlResponseParser::MODE_LIST);
        $response = $parser->parseResponse(ResponseLine::fromString('OK 1'), "---\n- a\nb");
    }

    public function testInvalidDictionary()
    {
        $this->expectException(ClientException::class);
        $parser = new YamlResponseParser(YamlResponseParser::MODE_DICT);
        $response = $parser->parseResponse(ResponseLine::fromString('OK 1'), "---\n: b\n");
    }
}
