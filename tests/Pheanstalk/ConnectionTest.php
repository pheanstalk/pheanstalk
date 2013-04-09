<?php

/**
 * Tests for the Pheanstalk_Connection.
 * Relies on a running beanstalkd server.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_ConnectionTest extends PHPUnit_Framework_TestCase
{
    const SERVER_HOST = 'localhost';
    const SERVER_PORT = '11300';
    const CONNECT_TIMEOUT = 2;

    /**
     * @expectedException \Pheanstalk\Exception\ConnectionException
     */
    public function testConnectionFailsToIncorrectPort()
    {
        $connection = new \Pheanstalk\Connection(
            self::SERVER_HOST,
            self::SERVER_PORT + 1
        );

        $command = new \Pheanstalk\Command\UseCommand('test');
        $connection->dispatchCommand($command);
    }

    public function testDispatchCommandSuccessful()
    {
        $connection = new \Pheanstalk\Connection(
            self::SERVER_HOST,
            self::SERVER_PORT
        );

        $command = new \Pheanstalk\Command\UseCommand('test');
        $response = $connection->dispatchCommand($command);

        $this->assertInstanceOf('Pheanstalk\IResponse', $response);
    }

    public function testConnectionResetIfSocketExceptionIsThrown()
    {
        $pheanstalk = new \Pheanstalk\Pheanstalk(
            self::SERVER_HOST,
            self::SERVER_PORT,
            self::CONNECT_TIMEOUT
        );

        $connection = $this->getMockBuilder('Pheanstalk\Connection')
                     ->disableOriginalConstructor()
                     ->getMock();

        $connection->expects($this->any())
             ->method('getHost')
             ->will($this->returnValue(self::SERVER_HOST));
        $connection->expects($this->any())
             ->method('getPort')
             ->will($this->returnValue( self::SERVER_PORT));
        $connection->expects($this->any())
             ->method('getConnectTimeout')
             ->will($this->returnValue(self::CONNECT_TIMEOUT));

        $pheanstalk->putInTube('testconnectionreset', __METHOD__);
        $pheanstalk->watchOnly('testconnectionreset');

        $pheanstalk->setConnection($connection);
        $connection->expects($this->once())
             ->method('dispatchCommand')
             ->will($this->throwException(new \Pheanstalk\Exception\SocketException('socket error simulated')));
        $job = $pheanstalk->reserve();

        $this->assertEquals(__METHOD__, $job->getData());
    }

    // ----------------------------------------
    // private

//    private function _getConnection()
//    {
//        return new \Pheanstalk\Connection(self::SERVER_HOST, self::SERVER_PORT);
//    }
}

