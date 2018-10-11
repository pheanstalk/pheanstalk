<?php

namespace Pheanstalk;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests exceptions thrown by ResponseParser implementations.
 *
 * @author  Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
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

    /**
     * @expectedException \Pheanstalk\Exception\CommandException
     */
    public function testPeekInvalidSubject()
    {
        new Command\PeekCommand('invalid');
    }

    public function testYamlResponseParserNotFound()
    {
        $this->expectServerExceptionForResponse(
            new YamlResponseParser(YamlResponseParser::MODE_DICT),
            'NOT_FOUND'
        );
    }

    public function testPauseTubeNotFound()
    {
        $this->expectServerExceptionForResponse(
            new Command\PauseTubeCommand('not-a-tube', 1),
            'NOT_FOUND'
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
            \Pheanstalk\Exception\ServerException::class
        );
    }
}
