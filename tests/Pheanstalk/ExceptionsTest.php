<?php

namespace Pheanstalk;

use Pheanstalk\Exception\ClientException;
use Pheanstalk\Exception\ServerException;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Tests the Pheanstalk exceptions, mainly for parse errors etc.
 *
 * @author  Paul Annesley
 */
class ExceptionsTest extends TestCase
{
    public function testPheanstalkException()
    {
        $e = new Exception();
        self::assertInstanceOf(Exception::class, $e);
    }

    public function testClientException()
    {
        $e = new ClientException();
        self::assertInstanceOf(Exception::class, $e);
    }

    public function testConnectionException()
    {
        $e = new Exception\ConnectionException(10, 'test');
        self::assertInstanceOf(ClientException::class, $e);
    }

    public function testCommandException()
    {
        $e = new Exception\CommandException('test');
        self::assertInstanceOf(ClientException::class, $e);
    }

    public function testServerException()
    {
        $e = new Exception\ServerException();
        self::assertInstanceOf(Exception::class, $e);
    }

    public function testServerBadFormatException()
    {
        $e = new Exception\ServerBadFormatException();
        self::assertInstanceOf(ServerException::class, $e);
    }

    public function testServerDrainingException()
    {
        $e = new Exception\ServerDrainingException();
        self::assertInstanceOf(ServerException::class, $e);
    }

    public function testServerInternalErrorException()
    {
        $e = new Exception\ServerInternalErrorException();
        self::assertInstanceOf(ServerException::class, $e);
    }

    public function testServerOutOfMemoryException()
    {
        $e = new Exception\ServerOutOfMemoryException();
        self::assertInstanceOf(ServerException::class, $e);
    }

    public function testServerUnknownCommandException()
    {
        $e = new Exception\ServerUnknownCommandException();
        self::assertInstanceOf(ServerException::class, $e);
    }
}
