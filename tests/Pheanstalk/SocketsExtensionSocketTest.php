<?php

/**
 * Tests the Pheanstalk SocketsExtensionSocket class
 *
 * @author DataDog <http://www.datadog.lt>
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_SocketsExtensionSocketTest extends PHPUnit_Framework_TestCase
{
    private $socket; // mock object

    public function setUp()
    {
        $this->socket = $this->getMockBuilder('Pheanstalk_Socket_SocketsExtensionSocket')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    function it_should_receive_write_commands()
    {
        $this->socket->expects($this->once())
            ->method('write')
            ->with("use my-tube\r\n");

        $this->socket->expects($this->once())
            ->method('getLine')
            ->will($this->returnValue("USING my-tube"));

        $conn = new Pheanstalk_Connection("any", 667);
        $conn->setSocket($this->socket);

        $command = new Pheanstalk_Command_UseCommand('my-tube');
        $conn->dispatchCommand($command);
    }

    /**
     * @test
     */
    function it_should_receive_commands_and_respond()
    {
        $this->socket->expects($this->once())
            ->method('write')
            ->with("stats-tube my-tube\r\n");

        $this->socket->expects($this->once())
            ->method('getLine')
            ->will($this->returnValue("OK 25"));

        $this->socket->expects($this->exactly(2))
            ->method('read')
            ->with($this->logicalOr(
                $this->equalTo(25),
                $this->equalTo(2)
            ))
            ->will($this->returnCallback(array($this, "mockTubeResponses")));

        $conn = new Pheanstalk_Connection("any", 667);
        $conn->setSocket($this->socket);

        $command = new Pheanstalk_Command_StatsTubeCommand('my-tube');
        $conn->dispatchCommand($command);
    }

    /**
     * @test
     */
    function it_should_be_able_to_communicate_with_actual_beanstalk_server()
    {
        $socket = new Pheanstalk_Socket_SocketsExtensionSocket('localhost', 11300);
        $conn = new Pheanstalk_Connection("any", 667);
        $conn->setSocket($this->socket);

        $command = new Pheanstalk_Command_UseCommand('my-tube');
        $response = $conn->dispatchCommand($command);

        $this->assertInstanceOf('Pheanstalk_Response', $response);
    }

    function mockTubeResponses($lengthRead)
    {
        $responses = array(
            25 => "---\nstat: 67",
            2 => Pheanstalk_Connection::CRLF
        );
        $idx = intval($lengthRead);
        if (!array_key_exists($idx, $responses)) {
            throw new Exception("Not expected length {$lengthRead} requested to be read..");
        }
        return $responses[$idx];
    }
}
