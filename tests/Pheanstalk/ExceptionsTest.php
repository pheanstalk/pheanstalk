<?php

/**
 * Tests the Pheanstalk exceptions, mainly for parse errors etc.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_ExceptionsTest extends PHPUnit_Framework_TestCase
{
    public function testPheanstalkException()
    {
        $e = new \Pheanstalk\Exception();
        $this->assertInstanceOf('Exception', $e);
    }

    public function testClientException()
    {
        $e = new \Pheanstalk\Exception\ClientException();
        $this->assertInstanceOf('\Pheanstalk\Exception', $e);
    }

    public function testConnectionException()
    {
        $e = new \Pheanstalk\Exception\ConnectionException(10, 'test');
        $this->assertInstanceOf('\Pheanstalk\Exception\ClientException', $e);
    }

    public function testCommandException()
    {
        $e = new \Pheanstalk\Exception\CommandException('test');
        $this->assertInstanceOf('\Pheanstalk\Exception\ClientException', $e);
    }

    public function testServerException()
    {
        $e = new \Pheanstalk\Exception\ServerException();
        $this->assertInstanceOf('\Pheanstalk\Exception', $e);
    }

    public function testServerBadFormatException()
    {
        $e = new \Pheanstalk\Exception\ServerBadFormatException();
        $this->assertInstanceOf('\Pheanstalk\Exception\ServerException', $e);
    }

    public function testServerDrainingException()
    {
        $e = new \Pheanstalk\Exception\ServerDrainingException();
        $this->assertInstanceOf('\Pheanstalk\Exception\ServerException', $e);
    }

    public function testServerInternalErrorException()
    {
        $e = new \Pheanstalk\Exception\ServerInternalErrorException();
        $this->assertInstanceOf('\Pheanstalk\Exception\ServerException', $e);
    }

    public function testServerOutOfMemoryException()
    {
        $e = new \Pheanstalk\Exception\ServerOutOfMemoryException();
        $this->assertInstanceOf('\Pheanstalk\Exception\ServerException', $e);
    }

    public function testServerUnknownCommandException()
    {
        $e = new \Pheanstalk\Exception\ServerUnknownCommandException();
        $this->assertInstanceOf('\Pheanstalk\Exception\ServerException', $e);
    }
}
