<?php

declare(strict_types=1);

namespace Pheanstalk\Tests;

use Pheanstalk\Exception;
use Pheanstalk\Exception\ClientException;
use Pheanstalk\Exception\ServerException;

/**
 * Tests the Pheanstalk exceptions, mainly for parse errors etc.
 */
class ExceptionsTest extends BaseTestCase
{
    public function testPheanstalkException()
    {
        $e = new Exception();
        $this->assertInstanceOf(Exception::class, $e);
    }

    public function testClientException()
    {
        $e = new ClientException();
        $this->assertInstanceOf(Exception::class, $e);
    }

    public function testConnectionException()
    {
        $e = new Exception\ConnectionException(10, 'test');
        $this->assertInstanceOf(ClientException::class, $e);
    }

    public function testCommandException()
    {
        $e = new Exception\CommandException('test');
        $this->assertInstanceOf(ClientException::class, $e);
    }

    public function testServerException()
    {
        $e = new Exception\ServerException();
        $this->assertInstanceOf(Exception::class, $e);
    }

    public function testServerBadFormatException()
    {
        $e = new Exception\ServerBadFormatException();
        $this->assertInstanceOf(ServerException::class, $e);
    }

    public function testServerDrainingException()
    {
        $e = new Exception\ServerDrainingException();
        $this->assertInstanceOf(ServerException::class, $e);
    }

    public function testServerInternalErrorException()
    {
        $e = new Exception\ServerInternalErrorException();
        $this->assertInstanceOf(ServerException::class, $e);
    }

    public function testServerOutOfMemoryException()
    {
        $e = new Exception\ServerOutOfMemoryException();
        $this->assertInstanceOf(ServerException::class, $e);
    }

    public function testServerUnknownCommandException()
    {
        $e = new Exception\ServerUnknownCommandException();
        $this->assertInstanceOf(ServerException::class, $e);
    }
}
