<?php

namespace Pheanstalk;

use Pheanstalk\Command\StatsCommand;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\ConnectionException;
use Pheanstalk\Exception\SocketException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Connection.
 * Relies on a running beanstalkd server.
 */
class ConnectionTest extends TestCase
{
    const CONNECT_TIMEOUT = 2;


    public function connectionProvider($test, $host = SERVER_HOST, $port = SERVER_PORT)
    {
        return [
            'stream' => [new Connection(new SocketFactory($host, $port, 1, SocketFactory::STREAM))],
            'fsockopen' => [new Connection(new SocketFactory($host, $port, 1, SocketFactory::FSOCKOPEN))],
            'socket' => [new Connection(new SocketFactory($host, $port, 1, SocketFactory::SOCKET))],
            'autodetect' =>[new Connection(new SocketFactory($host, $port, 1, SocketFactory::AUTODETECT))]
        ];
    }

    public function badPortConnectionProvider($test)
    {
        return $this->connectionProvider($test, SERVER_HOST, SERVER_PORT + 1);
    }

    public function badHostConnectionProvider($test)
    {
        return $this->connectionProvider($test, SERVER_HOST . 'abc', SERVER_PORT);
    }

    /**
     * @dataProvider badPortConnectionProvider
     */
    public function testConnectionFailsToIncorrectPort(Connection $connection)
    {
        $this->expectException(ConnectionException::class);
        $command = new Command\UseCommand('test');
        $connection->dispatchCommand($command);
    }


    /**
     * @dataProvider badHostConnectionProvider
     */
    public function testConnectionFailsToIncorrectHost(Connection $connection)
    {
        $this->expectException(ConnectionException::class);
        $command = new Command\UseCommand('test');
        $connection->dispatchCommand($command);
    }

    /**
     * @throws Exception\ClientException
     * @dataProvider connectionProvider
     */
    public function testDispatchCommandSuccessful(Connection $connection)
    {
        $command = new Command\UseCommand('test');
        $response = $connection->dispatchCommand($command);

        $this->assertInstanceOf(Contract\ResponseInterface::class, $response);
    }

    /**
     * @dataProvider connectionProvider
     */
    public function testDisconnect(Connection $connection)
    {
        $pheanstalk = new Pheanstalk(new Connection(new SocketFactory(SERVER_HOST, SERVER_PORT)));
        $baseCount = $pheanstalk->stats()['current-connections'];


        $this->assertEquals($baseCount, $pheanstalk->stats()['current-connections']);

        // initial connection
        $connection->dispatchCommand(new Command\StatsCommand());
        $this->assertEquals($baseCount + 1, $pheanstalk->stats()['current-connections']);

        // disconnect
        $connection->disconnect();
        $this->assertEquals($baseCount, $pheanstalk->stats()['current-connections']);

        // auto-reconnect
        $connection->dispatchCommand(new Command\StatsCommand());
        $this->assertEquals($baseCount + 1, $pheanstalk->stats()['current-connections']);


    }

}
