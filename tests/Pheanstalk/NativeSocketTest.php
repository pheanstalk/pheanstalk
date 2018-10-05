<?php

namespace Pheanstalk;

use Pheanstalk\Socket\NativeSocket;
use Pheanstalk\Socket\StreamFunctions;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Pheanstalk NativeSocket class.
 *
 * @author  SlNpacifist
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class NativeSocketTest extends TestCase
{
    const DEFAULT_HOST = 'localhost';
    const DEFAULT_PORT = 11300;
    const DEFAULT_CONNECTION_TIMEOUT = 0;

    private $_streamFunctions;

    protected static $Socket_StreamFunctions;

    public function setUp()
    {
        $instance = $this->getMockBuilder(\Pheanstalk\Socket\StreamFunctions::class)
            ->disableOriginalConstructor()
            ->getMock();

        $instance->expects($this->any())
            ->method('fsockopen')
            ->will($this->returnValue(true));

        self::$Socket_StreamFunctions = new StreamFunctions();
        self::$Socket_StreamFunctions->setInstance($instance);
        $this->_streamFunctions = $instance;
    }

    public function tearDown()
    {
        self::$Socket_StreamFunctions->unsetInstance();
    }

    /**
     * @expectedException \Pheanstalk\Exception\SocketException
     * @expectedExceptionMessage fwrite() failed to write data after
     */
    public function testWrite()
    {
        $this->_streamFunctions->expects($this->any())
            ->method('fwrite')
            ->will($this->returnValue(false));

        $socket = new NativeSocket(
            self::DEFAULT_HOST,
            self::DEFAULT_PORT,
            self::DEFAULT_CONNECTION_TIMEOUT
        );
        $socket->write('data');
    }

    /**
     * @expectedException \Pheanstalk\Exception\SocketException
     * @expectedExceptionMessage fread() returned false
     */
    public function testRead()
    {
        $this->_streamFunctions->expects($this->any())
             ->method('fread')
             ->will($this->returnValue(false));

        $socket = new NativeSocket(self::DEFAULT_HOST, self::DEFAULT_PORT, self::DEFAULT_CONNECTION_TIMEOUT);
        $socket->read(1);
    }

    public function testGetLine()
    {
        // Set the timeout.
        ini_set('default_socket_timeout', $timeout = 5);
        /**
         * We're trying to test for an infinite loop, since we can't do this we instead limit the number of invocations.
         */
        $this->_streamFunctions->expects($this->atMost(1000))
            ->method('fgets')
            ->will($this->returnCallback(function() {
                static $counter = 0;
                $counter++;
                usleep(5000);
                if ($counter > 2000) {
                    return '';
                }
                return false;
            }));

        $socket = new NativeSocket(self::DEFAULT_HOST, self::DEFAULT_PORT, self::DEFAULT_CONNECTION_TIMEOUT);

        $start = microtime(true);
        try {
            $socket->getLine();
        } catch (\Exception $e) {

        }

        // Check that get line takes no more than timeout + 1 seconds;
        // this is reasonable based on the fact that we only sleep for 1/20 of a second on each iteration.
        $this->assertLessThan(5 + 1, microtime(true) - $start, 'getLine did not respect the configured timeout');
    }

    /**
     * @throws Exception\ConnectionException
     * @expectedException \Pheanstalk\Exception\SocketException
     */
    public function testGetLineException()
    {
        // Set the timeout.
        ini_set('default_socket_timeout', $timeout = 5);
        $this->_streamFunctions->expects($this->atMost(1000))
            ->method('fgets')
            ->will($this->returnCallback(function() {
                usleep(5000);
                return false;
            }));

        $socket = new NativeSocket(self::DEFAULT_HOST, self::DEFAULT_PORT, self::DEFAULT_CONNECTION_TIMEOUT);

        $socket->getLine();
    }

}
