<?php

namespace Pheanstalk;

/**
 * Tests for the FailoverConnection.
 *
 * @author Hugo Chinchilla
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class FailoverConnectionTest extends \PHPUnit_Framework_TestCase
{
    const CONNECT_STRING = 'localhost:11299,localhost:11300';
    const CONNECT_TIMEOUT = 2;

    public function testConnectionFailoverIfPrimaryDown()
    {
        $pheanstalk = new PheanstalkFailover(
            self::CONNECT_STRING,
            self::CONNECT_TIMEOUT
        );

        // should have two connections
        $this->assertEquals(2, count($pheanstalk->getConnections()));

        // primary will fail
        $pheanstalk->setFailoverConnection(0, $this->_getConnection(true));
        // secondary will work
        $pheanstalk->setFailoverConnection(1, $this->_getConnection(false));

        // no exception should be raised
        $pheanstalk->watchOnly('testfailover');
    }

    /**
     * @expectedException \Pheanstalk\Exception\ConnectionException
     * @return [type] [description]
     */
    public function testConnectionFailoverIfAllDown()
    {
        $pheanstalk = new PheanstalkFailover(
            self::CONNECT_STRING,
            self::CONNECT_TIMEOUT
        );

        // should have two connections
        $this->assertEquals(2, count($pheanstalk->getConnections()));

        // primary will fail
        $pheanstalk->setFailoverConnection(0, $this->_getConnection(true));
        // secondary will fail too
        $pheanstalk->setFailoverConnection(1, $this->_getConnection(true));

        // an exception should be raised
        $pheanstalk->watchOnly('testfailover');
    }

    private function _getConnection($is_offline=true)
    {
        $connection = $this->getMockBuilder('\Pheanstalk\Connection')
                     ->disableOriginalConstructor()
                     ->getMock();

        $returnValue = $is_offline
            ? $this->throwException(new \Pheanstalk\Exception\ConnectionException(1, 'simulated offline'))
            : $this->returnValue(new Job(1, 'simulated data'));

        $connection->expects($this->any())
             ->method('dispatchCommand')
             ->will($returnValue);

        return $connection;
    }
}
