<?php

declare(strict_types=1);

namespace Pheanstalk\Tests;

use InvalidArgumentException;
use Pheanstalk\Command\BuryCommand;
use Pheanstalk\Command\DeleteCommand;
use Pheanstalk\Command\IgnoreCommand;
use Pheanstalk\Command\PauseTubeCommand;
use Pheanstalk\Command\PeekCommand;
use Pheanstalk\Command\PeekJobCommand;
use Pheanstalk\Command\PutCommand;
use Pheanstalk\Command\ReleaseCommand;
use Pheanstalk\Command\TouchCommand;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Exception\CommandException;
use Pheanstalk\Exception\ServerException;
use Pheanstalk\JobId;
use Pheanstalk\ResponseLine;
use Pheanstalk\YamlResponseParser;

/**
 * Tests exceptions thrown by ResponseParser implementations.
 */
class ResponseParserExceptionTest extends BaseTestCase
{
    public function testDeleteNotFound()
    {
        $this->expectServerExceptionForResponse(
            new DeleteCommand(new JobId(5)),
            'NOT_FOUND'
        );
    }

    public function testReleaseBuried()
    {
        $this->expectServerExceptionForResponse(
            new ReleaseCommand(new JobId(5), 1, 0),
            'BURIED'
        );
    }

    public function testReleaseNotFound()
    {
        $this->expectServerExceptionForResponse(
            new ReleaseCommand(new JobId(5), 1, 0),
            'NOT_FOUND'
        );
    }

    public function testBuryNotFound()
    {
        $this->expectServerExceptionForResponse(
            new BuryCommand(new JobId(5), 1),
            'NOT_FOUND'
        );
    }

    public function testIgnoreNotIgnored()
    {
        $this->expectServerExceptionForResponse(
            new IgnoreCommand('test'),
            'NOT_IGNORED'
        );
    }

    public function testTouchNotFound()
    {
        $this->expectServerExceptionForResponse(
            new TouchCommand(new JobId(5)),
            'NOT_FOUND'
        );
    }

    public function testPeekNotFound()
    {
        $this->expectServerExceptionForResponse(
            new PeekJobCommand(new JobId(5)),
            'NOT_FOUND'
        );
    }

    public function testPeekInvalidSubject()
    {
        $this->expectException(CommandException::class);
        new PeekCommand('invalid');
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
            new PauseTubeCommand('not-a-tube', 1),
            ResponseInterface::RESPONSE_NOT_FOUND
        );
    }

    public function testPutUnhandledResponse()
    {
        $this->expectExceptionForResponse(
            new PutCommand('data', 0, 0, 0),
            'unhandled response'
        );
    }

    private function expectExceptionForResponse(
        ResponseParserInterface $parser,
        string $response,
        string $type = Exception::class
    ) {
        $this->expectException($type);
        $parser->parseResponse(ResponseLine::fromString($response), null);
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
