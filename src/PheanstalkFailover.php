<?php

namespace Pheanstalk;

/**
 * Pheanstalk is a PHP client for the beanstalkd workqueue.
 * The Pheanstalk class is a simple facade for the various underlying components.
 *
 * @see http://github.com/kr/beanstalkd
 * @see http://xph.us/software/beanstalkd/
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class PheanstalkFailover extends Pheanstalk
{
    private $_connections = array();

    /**
     * @param string $hoststring
     * @param int    $connectTimeout
     */
    public function __construct($hoststring, $connectTimeout = null, $connectPersistent = false)
    {
        $hosts = explode(',', $hoststring);
        foreach ($hosts as $idx => $host) {
            list($host,$port) = explode(':', $host);
            $connection = new Connection($host, $port, $connectTimeout, $connectPersistent);
            $this->setFailoverConnection($idx, $connection);
        }
    }

    public function setFailoverConnection($idx, Connection $connection)
    {
        $this->_connections[$idx] = $connection;
    }

    public function getConnections()
    {
        return $this->_connections;
    }

    /**
     * Dispatches the specified command to the connection object.
     *
     * If a SocketException occurs, the connection is reset, and the command is
     * re-attempted once, if creating a conection fails, it will try with the
     * next available server, until one works or all servers fail.
     *
     * @param  Command  $command
     * @return Response
     */
    protected function _dispatch($command)
    {
        $lastException = null;

        foreach ($this->_connections as $idx => $connection) {
            try {
                $response = $connection->dispatchCommand($command);
            } catch (Exception\SocketException $e) {
                $this->_reconnect($idx);
                $response = $connection->dispatchCommand($command);
            } catch (Exception\ConnectionException $e) {
                $lastException = $e;
                continue;
            }
            break;
        }

        if (!isset($response) && $lastException) {
            throw $lastException;
        }

        return $response;
    }

    /**
     * Creates a new connection object, based on the existing connection object,
     * and re-establishes the used tube and watchlist.
     */
    protected function _reconnect($idx=0)
    {
        $new_connection = new Connection(
            $this->_connections[$idx]->getHost(),
            $this->_connections[$idx]->getPort(),
            $this->_connections[$idx]->getConnectTimeout()
        );

        $this->setFailoverConnection($idx, $new_connection);
        $this->_restoreWachedTubes();
    }
}
