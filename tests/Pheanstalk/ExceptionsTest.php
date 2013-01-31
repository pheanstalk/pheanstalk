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
        $e = new Pheanstalk_Exception();
        $this->assertInstanceOf('Exception', $e);
    }

    public function testClientException()
    {
        $e = new Pheanstalk_Exception_ClientException();
        $this->assertInstanceOf('Pheanstalk_Exception', $e);
    }

    public function testConnectionException()
    {
        $e = new Pheanstalk_Exception_ConnectionException(10, 'test');
        $this->assertInstanceOf('Pheanstalk_Exception_ClientException', $e);
    }

    public function testCommandException()
    {
        $e = new Pheanstalk_Exception_CommandException('test');
        $this->assertInstanceOf('Pheanstalk_Exception_ClientException', $e);
    }

    public function testServerException()
    {
        $e = new Pheanstalk_Exception_ServerException();
        $this->assertInstanceOf('Pheanstalk_Exception', $e);
    }

    public function testServerBadFormatException()
    {
        $e = new Pheanstalk_Exception_ServerBadFormatException();
        $this->assertInstanceOf('Pheanstalk_Exception_ServerException', $e);
    }

    public function testServerDrainingException()
    {
        $e = new Pheanstalk_Exception_ServerDrainingException();
        $this->assertInstanceOf('Pheanstalk_Exception_ServerException', $e);
    }

    public function testServerInternalErrorException()
    {
        $e = new Pheanstalk_Exception_ServerInternalErrorException();
        $this->assertInstanceOf('Pheanstalk_Exception_ServerException', $e);
    }

    public function testServerOutOfMemoryException()
    {
        $e = new Pheanstalk_Exception_ServerOutOfMemoryException();
        $this->assertInstanceOf('Pheanstalk_Exception_ServerException', $e);
    }

    public function testServerUnknownCommandException()
    {
        $e = new Pheanstalk_Exception_ServerUnknownCommandException();
        $this->assertInstanceOf('Pheanstalk_Exception_ServerException', $e);
    }
}
