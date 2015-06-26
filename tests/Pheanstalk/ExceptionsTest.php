<?php

namespace Pheanstalk;

/**
 * Tests the Pheanstalk exceptions, mainly for parse errors etc.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class ExceptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testPheanstalkException()
    {
        $e = new Exception();
        $this->assertInstanceOf('\Pheanstalk\Exception', $e);
    }

    public function testClientException()
    {
        $e = new Exception\ClientException();
        $this->assertInstanceOf('\Pheanstalk\Exception', $e);
    }

    public function testConnectionException()
    {
        $e = new Exception\ConnectionException(10, 'test');
        $this->assertInstanceOf('\Pheanstalk\Exception\ClientException', $e);
    }

    public function testCommandException()
    {
        $e = new Exception\CommandException('test');
        $this->assertInstanceOf('\Pheanstalk\Exception\ClientException', $e);
    }

    public function testServerException()
    {
        $e = new Exception\ServerException();
        $this->assertInstanceOf('\Pheanstalk\Exception', $e);
    }

    public function testServerBadFormatException()
    {
        $e = new Exception\ServerBadFormatException();
        $this->assertInstanceOf('\Pheanstalk\Exception\ServerException', $e);
    }

    public function testServerDrainingException()
    {
        $e = new Exception\ServerDrainingException();
        $this->assertInstanceOf('\Pheanstalk\Exception\ServerException', $e);
    }

    public function testServerInternalErrorException()
    {
        $e = new Exception\ServerInternalErrorException();
        $this->assertInstanceOf('\Pheanstalk\Exception\ServerException', $e);
    }

    public function testServerOutOfMemoryException()
    {
        $e = new Exception\ServerOutOfMemoryException();
        $this->assertInstanceOf('\Pheanstalk\Exception\ServerException', $e);
    }

    public function testServerUnknownCommandException()
    {
        $e = new Exception\ServerUnknownCommandException();
        $this->assertInstanceOf('\Pheanstalk\Exception\ServerException', $e);
    }
}
