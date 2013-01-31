<?php

/**
 * Tests the Pheanstalk NativeSocket class
 *
 * @author SlNpacifist
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_NativeSocketTest extends PHPUnit_Framework_TestCase
{
    const DEFAULT_HOST = 'localhost';
    const DEFAULT_PORT = 11300;
    const DEFAULT_CONNECTION_TIMEOUT = 0;

    private $_streamFunctions;

    protected static $Pheanstalk_Socket_StreamFunctions;

    public function setUp()
    {
        $instance = $this->getMockBuilder('Pheanstalk_Socket_StreamFunctions')
            ->disableOriginalConstructor()
            ->getMock();

        $instance->expects($this->any())
            ->method('fsockopen')
            ->will($this->returnValue(true));

        self::$Pheanstalk_Socket_StreamFunctions = new Pheanstalk_Socket_StreamFunctions();
        self::$Pheanstalk_Socket_StreamFunctions->setInstance($instance);
        $this->_streamFunctions = $instance;
    }

    public function tearDown()
    {
        self::$Pheanstalk_Socket_StreamFunctions->unsetInstance();
    }

    /**
     * @expectedException Pheanstalk_Exception_SocketException
     * @expectedExceptionMessage fwrite() failed to write data after
     */
    public function testWrite()
    {
        $this->_streamFunctions->expects($this->any())
            ->method('fwrite')
            ->will($this->returnValue(false));

        $socket = new Pheanstalk_Socket_NativeSocket(
            self::DEFAULT_HOST,
            self::DEFAULT_HOST,
            self::DEFAULT_CONNECTION_TIMEOUT
        );
        $socket->write('data');
    }

    /**
     * @expectedException Pheanstalk_Exception_SocketException
     * @expectedExceptionMessage fread() returned false
     */
    public function testRead()
    {
        $this->_streamFunctions->expects($this->any())
             ->method('fread')
             ->will($this->returnValue(false));

        $socket = new Pheanstalk_Socket_NativeSocket(self::DEFAULT_HOST, self::DEFAULT_HOST, self::DEFAULT_CONNECTION_TIMEOUT);
        $socket->read(1);
    }
}
