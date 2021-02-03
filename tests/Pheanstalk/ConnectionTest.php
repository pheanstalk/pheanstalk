<?php
declare(strict_types=1);

namespace Pheanstalk\Tests;

use Pheanstalk\Command\StatsCommand;
use Pheanstalk\Command\UseCommand;
use Pheanstalk\Connection;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Exception\ClientException;
use Pheanstalk\Exception\ConnectionException;
use Pheanstalk\Pheanstalk;
use Pheanstalk\SocketFactory;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Tests for the Connection.
 * Relies on a running beanstalkd server.
 */
class ConnectionTest extends BaseTestCase
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
     * @throws ClientException
     */
    public function testConnectionFailsToIncorrectPort(Connection $connection)
    {
        $this->expectException(ConnectionException::class);
        $command = new UseCommand('test');
        $connection->dispatchCommand($command);
    }

    /**
     * @dataProvider badHostConnectionProvider
     *
     * @param Connection $connection
     *
     * @throws ClientException
     */
    public function testConnectionFailsToIncorrectHost(Connection $connection)
    {
        $this->expectException(ConnectionException::class);
        $command = new UseCommand('test');
        $connection->dispatchCommand($command);
    }

    /**
     * @param Connection $connection
     *
     * @throws ClientException
     * @dataProvider connectionProvider
     */
    public function testDispatchCommandSuccessful(Connection $connection)
    {
        $command = new UseCommand('test');
        $response = $connection->dispatchCommand($command);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @dataProvider connectionProvider
     *
     * @param Connection $connection
     *
     * @throws ClientException
     */
    public function testDisconnect(Connection $connection)
    {
        $pheanstalk = new Pheanstalk(new Connection(new SocketFactory(SERVER_HOST, SERVER_PORT)));
        $baseCount = $pheanstalk->stats()['current-connections'];

        $this->assertSame($baseCount, $pheanstalk->stats()['current-connections']);

        // initial connection
        $connection->dispatchCommand(new StatsCommand());
        $this->assertEquals($baseCount + 1, $pheanstalk->stats()['current-connections']);

        // disconnect
        $connection->disconnect();
        $this->assertSame($baseCount, $pheanstalk->stats()['current-connections']);

        // auto-reconnect
        $connection->dispatchCommand(new StatsCommand());
        $this->assertEquals($baseCount + 1, $pheanstalk->stats()['current-connections']);
    }
}
