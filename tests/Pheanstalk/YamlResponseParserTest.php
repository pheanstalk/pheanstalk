<?php


namespace Pheanstalk;

use Pheanstalk\Exception\ClientException;
use PHPUnit\Framework\TestCase;

class YamlResponseParserTest extends TestCase
{

    public function testList()
    {
        $parser = new YamlResponseParser(YamlResponseParser::MODE_LIST);
        $response = $parser->parseResponse('OK 1', "---\n- a\n- b");
        $this->assertEquals(['a', 'b'], iterator_to_array($response));
    }

    public function testInvalidList()
    {
        $this->expectException(ClientException::class);
        $parser = new YamlResponseParser(YamlResponseParser::MODE_LIST);
        $response = $parser->parseResponse('OK 1', "---\n- a\nb");
    }

    public function testInvalidDictionary()
    {
        $this->expectException(ClientException::class);
        $parser = new YamlResponseParser(YamlResponseParser::MODE_DICT);
        $response = $parser->parseResponse('OK 1', "---\n: b\n");
    }
}
