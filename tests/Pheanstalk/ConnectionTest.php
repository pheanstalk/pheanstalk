<?php

namespace Pheanstalk;

use Pheanstalk\Exception\ConnectionException;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

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
     *
     * @param Connection $connection
     *
     * @throws Exception\ClientException
     */
    public function testConnectionFailsToIncorrectPort(Connection $connection)
    {
        $this->expectException(ConnectionException::class);
        $command = new Command\UseCommand('test');
        $connection->dispatchCommand($command);
    }

    /**
     * @dataProvider badHostConnectionProvider
     *
     * @param Connection $connection
     *
     * @throws Exception\ClientException
     */
    public function testConnectionFailsToIncorrectHost(Connection $connection)
    {
        $this->expectException(ConnectionException::class);
        $command = new Command\UseCommand('test');
        $connection->dispatchCommand($command);
    }

    /**
     * @param Connection $connection
     *
     * @throws Exception\ClientException
     * @dataProvider connectionProvider
     */
    public function testDispatchCommandSuccessful(Connection $connection)
    {
        $command = new Command\UseCommand('test');
        $response = $connection->dispatchCommand($command);

        self::assertInstanceOf(Contract\ResponseInterface::class, $response);
    }

    /**
     * @dataProvider connectionProvider
     *
     * @param Connection $connection
     *
     * @throws Exception\ClientException
     */
    public function testDisconnect(Connection $connection)
    {
        $pheanstalk = new Pheanstalk(new Connection(new SocketFactory(SERVER_HOST, SERVER_PORT)));
        $baseCount = $pheanstalk->stats()['current-connections'];

        self::assertSame($baseCount, $pheanstalk->stats()['current-connections']);

        // initial connection
        $connection->dispatchCommand(new Command\StatsCommand());
        self::assertEquals($baseCount + 1, $pheanstalk->stats()['current-connections']);

        // disconnect
        $connection->disconnect();
        self::assertSame($baseCount, $pheanstalk->stats()['current-connections']);

        // auto-reconnect
        $connection->dispatchCommand(new Command\StatsCommand());
        self::assertEquals($baseCount + 1, $pheanstalk->stats()['current-connections']);
    }
}
