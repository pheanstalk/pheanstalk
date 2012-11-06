<?php

/**
 * Tests for the Pheanstalk_Connection.
 * Relies on a running beanstalkd server.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_ConnectionTest
    extends PHPUnit_Framework_TestCase
{
    const SERVER_HOST = 'localhost';
    const SERVER_PORT = '11300';
    const CONNECT_TIMEOUT = 2;

    /**
     * @expectedException Pheanstalk_Exception_ConnectionException
     */
    public function testConnectionFailsToIncorrectPort()
    {
        $connection = new Pheanstalk_Connection(
            self::SERVER_HOST,
            self::SERVER_PORT + 1
        );

        $command = new Pheanstalk_Command_UseCommand('test');
        
        $connection->dispatchCommand($command);
    }

    public function testDispatchCommandSuccessful()
    {
        $connection = new Pheanstalk_Connection(
            self::SERVER_HOST,
            self::SERVER_PORT
        );

        $command = new Pheanstalk_Command_UseCommand('test');
        $response = $connection->dispatchCommand($command);

        $this->assertInstanceOf('Pheanstalk_Response', $response);
    }

    public function testConnectionResetIfSocketExceptionIsThrown()
    {
        $pheanstalk = new Pheanstalk(
            self::SERVER_HOST,
            self::SERVER_PORT,
            self::CONNECT_TIMEOUT
        );

        $this->getMock('Pheanstalk_Connection', array(), array(), 'MockPheanstalk_Connection');
        $connection = new MockPheanstalk_Connection('');
        $connection->returns('getHost', self::SERVER_HOST);
        $connection->returns('getPort', self::SERVER_PORT);
        $connection->returns('getConnectTimeout', self::CONNECT_TIMEOUT);
        $connection->throwOn(
            'dispatchCommand',
            new Pheanstalk_Exception_SocketException('socket error simulated')
        );

        $pheanstalk->putInTube('testconnectionreset', __METHOD__);
        $pheanstalk->watchOnly('testconnectionreset');

        $pheanstalk->setConnection($connection);
        $connection->expectOnce('dispatchCommand');
        $job = $pheanstalk->reserve();

        $this->assertEquals(__METHOD__, $job->getData());
    }

    // ----------------------------------------
    // private

    private function _getConnection()
    {
        return new Pheanstalk_Connection(self::SERVER_HOST, self::SERVER_PORT);
    }
}

