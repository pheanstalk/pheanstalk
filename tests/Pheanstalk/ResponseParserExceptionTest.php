<?php

declare(strict_types=1);

namespace Pheanstalk;

use InvalidArgumentException;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\CommandException;
use Pheanstalk\Exception\ServerException;
use PHPUnit\Framework\TestCase;

/**
 * Tests exceptions thrown by ResponseParser implementations.
 *
 * @author  Paul Annesley
 */
class ResponseParserExceptionTest extends TestCase
{
    public function testDeleteNotFound()
    {
        $this->expectServerExceptionForResponse(
            new Command\DeleteCommand(new JobId(5)),
            'NOT_FOUND'
        );
    }

    public function testReleaseBuried()
    {
        $this->expectServerExceptionForResponse(
            new Command\ReleaseCommand(new JobId(5), 1, 0),
            'BURIED'
        );
    }

    public function testReleaseNotFound()
    {
        $this->expectServerExceptionForResponse(
            new Command\ReleaseCommand(new JobId(5), 1, 0),
            'NOT_FOUND'
        );
    }

    public function testBuryNotFound()
    {
        $this->expectServerExceptionForResponse(
            new Command\BuryCommand(new JobId(5), 1),
            'NOT_FOUND'
        );
    }

    public function testIgnoreNotIgnored()
    {
        $this->expectServerExceptionForResponse(
            new Command\IgnoreCommand('test'),
            'NOT_IGNORED'
        );
    }

    public function testTouchNotFound()
    {
        $this->expectServerExceptionForResponse(
            new Command\TouchCommand(new JobId(5)),
            'NOT_FOUND'
        );
    }

    public function testPeekNotFound()
    {
        $this->expectServerExceptionForResponse(
            new Command\PeekJobCommand(new JobId(5)),
            'NOT_FOUND'
        );
    }

    public function testPeekInvalidSubject()
    {
        $this->expectException(CommandException::class);
        new Command\PeekCommand('invalid');
    }

    public function testYamlResponseParseInvalidMode()
    {
        $this->expectException(InvalidArgumentException::class);
        new YamlResponseParser('test');
    }

    public function testYamlResponseParserNotFound()
    {
        $this->expectServerExceptionForResponse(
            new YamlResponseParser(YamlResponseParser::MODE_DICT),
            ResponseInterface::RESPONSE_NOT_FOUND
        );
    }


    public function testYamlResponseParserUnhandledResponse()
    {
        $this->expectServerExceptionForResponse(
            new YamlResponseParser(YamlResponseParser::MODE_DICT),
            ResponseInterface::RESPONSE_OUT_OF_MEMORY
        );
    }

    public function testPauseTubeNotFound()
    {
        $this->expectServerExceptionForResponse(
            new Command\PauseTubeCommand('not-a-tube', 1),
            ResponseInterface::RESPONSE_NOT_FOUND
        );
    }

    public function testPutUnhandledResponse()
    {
        $this->expectExceptionForResponse(
            new Command\PutCommand('data', 0, 0, 0),
            'unhandled response'
        );
    }

    private function expectExceptionForResponse(
        ResponseParserInterface $parser,
        string $response,
        string $type = Exception::class
    ) {
        $this->expectException($type);
        $parser->parseResponse($response, null);
    }

    private function expectServerExceptionForResponse(ResponseParserInterface $parser, string $response)
    {
        $this->expectExceptionForResponse(
            $parser,
            $response,
            ServerException::class
        );
    }
}
