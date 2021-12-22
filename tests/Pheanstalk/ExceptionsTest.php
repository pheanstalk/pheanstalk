<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Exception\ClientException;
use Pheanstalk\Exception\ServerException;
use PHPUnit\Framework\TestCase;

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
