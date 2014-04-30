<?php

namespace Pheanstalk;

/**
 * Tests for Command implementations.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class CommandTest extends \PHPUnit_Framework_TestCase
{
    public function testBury()
    {
        $command = new Command\BuryCommand($this->_mockJob(5), 2);
        $this->_assertCommandLine($command, 'bury 5 2');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('BURIED', null),
            Response::RESPONSE_BURIED
        );
    }

    public function testDelete()
    {
        $command = new Command\DeleteCommand($this->_mockJob(5));
        $this->_assertCommandLine($command, 'delete 5');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('DELETED', null),
            Response::RESPONSE_DELETED
        );
    }

    public function testIgnore()
    {
        $command = new Command\IgnoreCommand('tube1');
        $this->_assertCommandLine($command, 'ignore tube1');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('WATCHING 2', null),
            Response::RESPONSE_WATCHING,
            array('count' => 2)
        );
    }

    public function testKick()
    {
        $command = new Command\KickCommand(5);
        $this->_assertCommandLine($command, 'kick 5');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('KICKED 2', null),
            Response::RESPONSE_KICKED,
            array('kicked' => 2)
        );
    }

    public function testKickJob()
    {
        $command = new Command\KickJobCommand($this->_mockJob(5));
        $this->_assertCommandLine($command, 'kick-job 5');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('KICKED', null),
            Response::RESPONSE_KICKED
        );
    }

    public function testListTubesWatched()
    {
        $command = new Command\ListTubesWatchedCommand();
        $this->_assertCommandLine($command, 'list-tubes-watched');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('OK 16', "---\n- one\n- two\n"),
            Response::RESPONSE_OK,
            array('one', 'two')
        );
    }

    public function testListTubeUsed()
    {
        $command = new Command\ListTubeUsedCommand();
        $this->_assertCommandLine($command, 'list-tube-used');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('USING default', null),
            Response::RESPONSE_USING,
            array('tube' => 'default')
        );
    }

    public function testPut()
    {
        $command = new Command\PutCommand('data', 5, 6, 7);
        $this->_assertCommandLine($command, 'put 5 6 7 4', true);
        $this->assertEquals($command->getData(), 'data');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('INSERTED 4', null),
            Response::RESPONSE_INSERTED,
            array('id' => '4')
        );
    }

    public function testRelease()
    {
        $job = $this->_mockJob(3);
        $command = new Command\ReleaseCommand($job, 1, 0);
        $this->_assertCommandLine($command, 'release 3 1 0');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('RELEASED', null),
            Response::RESPONSE_RELEASED
        );
    }

    public function testReserve()
    {
        $command = new Command\ReserveCommand();
        $this->_assertCommandLine($command, 'reserve');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('RESERVED 5 9', "test data"),
            Response::RESPONSE_RESERVED,
            array('id' => 5, 'jobdata' => 'test data')
        );
    }

    public function testUse()
    {
        $command = new Command\UseCommand('tube5');
        $this->_assertCommandLine($command, 'use tube5');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('USING tube5', null),
            Response::RESPONSE_USING,
            array('tube' => 'tube5')
        );
    }

    public function testWatch()
    {
        $command = new Command\WatchCommand('tube6');
        $this->_assertCommandLine($command, 'watch tube6');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('WATCHING 3', null),
            Response::RESPONSE_WATCHING,
            array('count' => '3')
        );
    }

    public function testReserveWithTimeout()
    {
        $command = new Command\ReserveCommand(10);
        $this->_assertCommandLine($command, 'reserve-with-timeout 10');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('TIMED_OUT', null),
            Response::RESPONSE_TIMED_OUT
        );
    }

    public function testTouch()
    {
        $command = new Command\TouchCommand($this->_mockJob(5));
        $this->_assertCommandLine($command, 'touch 5');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('TOUCHED', null),
            Response::RESPONSE_TOUCHED
        );
    }

    public function testListTubes()
    {
        $command = new Command\ListTubesCommand();
        $this->_assertCommandLine($command, 'list-tubes');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('OK 16', "---\n- one\n- two\n"),
            Response::RESPONSE_OK,
            array('one', 'two')
        );
    }

    public function testPeek()
    {
        $command = new Command\PeekCommand(5);
        $this->_assertCommandLine($command, 'peek 5');

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('FOUND 5 9', "test data"),
            Response::RESPONSE_FOUND,
            array('id' => 5, 'jobdata' => 'test data')
        );
    }

    public function testPeekReady()
    {
        $command = new Command\PeekCommand('ready');
        $this->_assertCommandLine($command, 'peek-ready');
    }

    public function testPeekDelayed()
    {
        $command = new Command\PeekCommand('delayed');
        $this->_assertCommandLine($command, 'peek-delayed');
    }

    public function testPeekBuried()
    {
        $command = new Command\PeekCommand('buried');
        $this->_assertCommandLine($command, 'peek-buried');
    }

    public function testStatsJob()
    {
        $command = new Command\StatsJobCommand(5);
        $this->_assertCommandLine($command, 'stats-job 5');

        $data = "---\r\nid: 8\r\ntube: test\r\nstate: delayed\r\n";

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('OK '.strlen($data), $data),
            Response::RESPONSE_OK,
            array('id' => '8', 'tube' => 'test', 'state' => 'delayed')
        );
    }

    public function testStatsTube()
    {
        $command = new Command\StatsTubeCommand('test');
        $this->_assertCommandLine($command, 'stats-tube test');

        $data = "---\r\nname: test\r\ncurrent-jobs-ready: 5\r\n";

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('OK '.strlen($data), $data),
            Response::RESPONSE_OK,
            array('name' => 'test', 'current-jobs-ready' => '5')
        );
    }

    public function testStats()
    {
        $command = new Command\StatsCommand();
        $this->_assertCommandLine($command, 'stats');

        $data = "---\r\npid: 123\r\nversion: 1.3\r\n";

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('OK '.strlen($data), $data),
            Response::RESPONSE_OK,
            array('pid' => '123', 'version' => '1.3')
        );
    }

    public function testPauseTube()
    {
        $command = new Command\PauseTubeCommand('testtube7', 10);
        $this->_assertCommandLine($command, 'pause-tube testtube7 10');
        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('PAUSED', null),
            Response::RESPONSE_PAUSED
        );
    }

    // ----------------------------------------

    /**
     * @param Command
     * @param string $expected
     */
    private function _assertCommandLine($command, $expected, $expectData = false)
    {
        $this->assertEquals($command->getCommandLine(), $expected);

        if ($expectData) {
            $this->assertTrue($command->hasData(), 'should have data');
        } else {
            $this->assertFalse($command->hasData(), 'should have no data');
        }
    }

    /**
     * @param Response $response
     * @param string   $expectName
     */
    private function _assertResponse($response, $expectName, $data = array())
    {
        $this->assertEquals($response->getResponseName(), $expectName);
        $this->assertEquals($response->getArrayCopy(), $data);
    }

    /**
     * @param int $id
     */
    private function _mockJob($id)
    {
        $job = $this->getMockBuilder('\Pheanstalk\Job')
                     ->disableOriginalConstructor()
                     ->getMock();

        $job->expects($this->any())
                 ->method('getId')
                 ->will($this->returnValue($id));

        return $job;
    }
}
