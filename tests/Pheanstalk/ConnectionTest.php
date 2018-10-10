<?php

namespace Pheanstalk;

use Pheanstalk\Command\StatsCommand;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\SocketException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Connection.
 * Relies on a running beanstalkd server.
 *
 * @author  Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class ConnectionTest extends TestCase
{
    const CONNECT_TIMEOUT = 2;

    /**
     * @expectedException \Pheanstalk\Exception\ConnectionException
     */
    public function testConnectionFailsToIncorrectPort()
    {
        $connection = new Connection(new SocketFactory(
            SERVER_HOST,
                SERVER_PORT + 1
        ));

        $command = new Command\UseCommand('test');
        $connection->dispatchCommand($command);
    }

    public function testDispatchCommandSuccessful()
    {
        $connection = new Connection(new SocketFactory(
            SERVER_HOST,
            SERVER_PORT
        ));

        $command = new Command\UseCommand('test');
        $response = $connection->dispatchCommand($command);

        $this->assertInstanceOf(Contract\ResponseInterface::class, $response);
    }

    public function testDisconnect()
    {
        $pheanstalk = new Pheanstalk($this->_getConnection());
        $this->assertEquals(1, $pheanstalk->stats()['current-connections']);

        $connection = $this->_getConnection();
        $this->assertEquals(1, $pheanstalk->stats()['current-connections']);

        // initial connection
        $connection->dispatchCommand(new Command\StatsCommand());
        $this->assertEquals(2, $pheanstalk->stats()['current-connections']);

        // disconnect
        $connection->disconnect();
        $this->assertEquals(1, $pheanstalk->stats()['current-connections']);

        // auto-reconnect
        $connection->dispatchCommand(new Command\StatsCommand());
        $this->assertEquals(2, $pheanstalk->stats()['current-connections']);
    }

    // ----------------------------------------
    // private

    private function _getConnection()
    {
        return new Connection(new SocketFactory(SERVER_HOST, SERVER_PORT));
    }
}
