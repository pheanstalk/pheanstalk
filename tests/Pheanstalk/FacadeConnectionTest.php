<?php

/**
 * Tests the Pheanstalk facade (the base class).
 * Relies on a running beanstalkd server.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_FacadeConnectionTest extends PHPUnit_Framework_TestCase
{
    const SERVER_HOST = 'localhost';

    public function testVersion()
    {
        // Examples: 1.0.0, 2.0.0, 2.0.0-rc1
        $this->assertRegExp(
            '/\d+\.\d+\.\d+(?:-\w+)?/',
            Pheanstalk_Pheanstalk::VERSION
        );
    }

    public function testUseTube()
    {
        $pheanstalk = $this->_getFacade();

        $this->assertEquals($pheanstalk->listTubeUsed(), 'default');
        $this->assertEquals($pheanstalk->listTubeUsed(true), 'default');

        $pheanstalk->useTube('test');
        $this->assertEquals($pheanstalk->listTubeUsed(), 'test');
        $this->assertEquals($pheanstalk->listTubeUsed(true), 'test');
    }

    public function testWatchlist()
    {
        $pheanstalk = $this->_getFacade();

        $this->assertEquals($pheanstalk->listTubesWatched(), array('default'));
        $this->assertEquals($pheanstalk->listTubesWatched(true), array('default'));

        $pheanstalk->watch('test');
        $this->assertEquals($pheanstalk->listTubesWatched(), array('default', 'test'));
        $this->assertEquals($pheanstalk->listTubesWatched(true), array('default', 'test'));

        $pheanstalk->ignore('default');
        $this->assertEquals($pheanstalk->listTubesWatched(), array('test'));
        $this->assertEquals($pheanstalk->listTubesWatched(true), array('test'));

        $pheanstalk->watchOnly('default');
        $this->assertEquals($pheanstalk->listTubesWatched(), array('default'));
        $this->assertEquals($pheanstalk->listTubesWatched(true), array('default'));
    }

    /**
     * @expectedException Pheanstalk_Exception
     */
    public function testIgnoreLastTube()
    {
        $pheanstalk = $this->_getFacade();

        $pheanstalk->ignore('default');
    }

    public function testPutReserveAndDeleteData()
    {
        $pheanstalk = $this->_getFacade();

        $id = $pheanstalk->put(__METHOD__);

        $this->assertInternalType('int', $id);

        // reserve a job - can't assume it is the one just added
        $job = $pheanstalk->reserve();

        $this->assertInstanceOf('Pheanstalk_Job', $job);

        // delete the reserved job
        $pheanstalk->delete($job);

        // put a job into an unused tube
        $id = $pheanstalk->putInTube('test', __METHOD__);

        $this->assertInternalType('int', $id);

        // reserve a job from an unwatched tube - can't assume it is the one just added
        $job = $pheanstalk->reserveFromTube('test');

        $this->assertInstanceOf('Pheanstalk_Job', $job);

        // delete the reserved job
        $pheanstalk->delete($job);
    }

    public function testRelease()
    {
        $pheanstalk = $this->_getFacade();

        $pheanstalk->put(__METHOD__);
        $job = $pheanstalk->reserve();
        $pheanstalk->release($job);
    }

    public function testPutBuryAndKick()
    {
        $pheanstalk = $this->_getFacade();

        $id = $pheanstalk->put(__METHOD__);

        $this->assertInternalType('int', $id);

        // reserve a job - can't assume it is the one just added
        $job = $pheanstalk->reserve();

        $this->assertInstanceOf('Pheanstalk_Job', $job);

        // bury the reserved job
        $pheanstalk->bury($job);

        // kick up to one job
        $kickedCount = $pheanstalk->kick(1);

        $this->assertInternalType('int', $kickedCount);
        $this->assertEquals($kickedCount, 1,
            'there should be at least one buried (or delayed) job: %s');
    }

    /**
     * @expectedException Pheanstalk_Exception
     */
    public function testPutJobTooBig()
    {
        $pheanstalk = $this->_getFacade();


        $pheanstalk->put(str_repeat('0', 0x10000));
    }

    public function testTouch()
    {
        $pheanstalk = $this->_getFacade();

        $pheanstalk->put(__METHOD__);
        $job = $pheanstalk->reserve();
        $pheanstalk->touch($job);
    }

    public function testListTubes()
    {
        $pheanstalk = $this->_getFacade();

        $this->assertInternalType('array', $pheanstalk->listTubes());
        $this->assertTrue(in_array('default', $pheanstalk->listTubes()));

        $pheanstalk->useTube('test1');
        $this->assertTrue(in_array('test1', $pheanstalk->listTubes()));

        $pheanstalk->watch('test2');
        $this->assertTrue(in_array('test2', $pheanstalk->listTubes()));
    }

    public function testPeek()
    {
        $pheanstalk = $this->_getFacade();

        $id = $pheanstalk
            ->useTube('testpeek')
            ->watch('testpeek')
            ->ignore('default')
            ->put('test');

        $job = $pheanstalk->peek($id);

        $this->assertEquals($job->getData(), 'test');

        // put job in an unused tube
        $id = $pheanstalk->putInTube('testpeek2', 'test2');

        $job = $pheanstalk->peek($id);

        $this->assertEquals($job->getData(), 'test2');
    }

    public function testPeekReady()
    {
        $pheanstalk = $this->_getFacade();

        $id = $pheanstalk
            ->useTube('testpeekready')
            ->watch('testpeekready')
            ->ignore('default')
            ->put('test');

        $job = $pheanstalk->peekReady();

        $this->assertEquals($job->getData(), 'test');

        // put job in an unused tube
        $id = $pheanstalk->putInTube('testpeekready2', 'test2');

        // use default tube
        $pheanstalk->useTube('default');

        // peek the tube that has the job
        $job = $pheanstalk->peekReady('testpeekready2');

        $this->assertEquals($job->getData(), 'test2');
    }

    public function testPeekDelayed()
    {
        $pheanstalk = $this->_getFacade();

        $id = $pheanstalk
            ->useTube('testpeekdelayed')
            ->watch('testpeekdelayed')
            ->ignore('default')
            ->put('test', 0, 2);

        $job = $pheanstalk->peekDelayed();

        $this->assertEquals($job->getData(), 'test');

        // put job in an unused tube
        $id = $pheanstalk->putInTube('testpeekdelayed2', 'test2');

        // use default tube
        $pheanstalk->useTube('default');

        // peek the tube that has the job
        $job = $pheanstalk->peekReady('testpeekdelayed2');

        $this->assertEquals($job->getData(), 'test2');
    }

    public function testPeekBuried()
    {
        $pheanstalk = $this->_getFacade();

        $id = $pheanstalk
            ->useTube('testpeekburied')
            ->watch('testpeekburied')
            ->ignore('default')
            ->put('test');

        $job = $pheanstalk->reserve($id);
        $pheanstalk->bury($job);

        $job = $pheanstalk->peekBuried();

        $this->assertEquals($job->getData(), 'test');

        // put job in an unused tube
        $id = $pheanstalk->putInTube('testpeekburied2', 'test2');

        // use default tube
        $pheanstalk->useTube('default');

        // peek the tube that has the job
        $job = $pheanstalk->peekReady('testpeekburied2');

        $this->assertEquals($job->getData(), 'test2');
    }

    public function testStatsJob()
    {
        $pheanstalk = $this->_getFacade();

        $id = $pheanstalk
            ->useTube('teststatsjob')
            ->watch('teststatsjob')
            ->ignore('default')
            ->put('test');

        $stats = $pheanstalk->statsJob($id);

        $this->assertEquals($stats->id, $id);
        $this->assertEquals($stats->tube, 'teststatsjob');
        $this->assertEquals($stats->state, 'ready');
        $this->assertEquals($stats->pri, Pheanstalk_Pheanstalk::DEFAULT_PRIORITY);
        $this->assertEquals($stats->delay, Pheanstalk_Pheanstalk::DEFAULT_DELAY);
        $this->assertEquals($stats->ttr, Pheanstalk_Pheanstalk::DEFAULT_TTR);
        $this->assertEquals($stats->timeouts, 0);
        $this->assertEquals($stats->releases, 0);
        $this->assertEquals($stats->buries, 0);
        $this->assertEquals($stats->kicks, 0);
    }

    public function testStatsJobWithJobObject()
    {
        $pheanstalk = $this->_getFacade();

        $pheanstalk
            ->useTube('teststatsjobwithjobobject')
            ->watch('teststatsjobwithjobobject')
            ->ignore('default')
            ->put('test');

        $job = $pheanstalk
            ->reserve();

        $stats = $pheanstalk->statsJob($job);

        $this->assertEquals($stats->id, $job->getId());
        $this->assertEquals($stats->tube, 'teststatsjobwithjobobject');
        $this->assertEquals($stats->state, 'reserved');
        $this->assertEquals($stats->pri, Pheanstalk_Pheanstalk::DEFAULT_PRIORITY);
        $this->assertEquals($stats->delay, Pheanstalk_Pheanstalk::DEFAULT_DELAY);
        $this->assertEquals($stats->ttr, Pheanstalk_Pheanstalk::DEFAULT_TTR);
        $this->assertEquals($stats->timeouts, 0);
        $this->assertEquals($stats->releases, 0);
        $this->assertEquals($stats->buries, 0);
        $this->assertEquals($stats->kicks, 0);
    }

    public function testStatsTube()
    {
        $pheanstalk = $this->_getFacade();

        $tube = 'test-stats-tube';
        $pheanstalk->useTube($tube);

        $stats = $pheanstalk->statsTube($tube);

        $this->assertEquals($stats->name, $tube);
        $this->assertEquals($stats->current_jobs_reserved, '0');
    }

    public function testStats()
    {
        $pheanstalk = $this->_getFacade();

        $stats = $pheanstalk->useTube('test-stats')->stats();

        $properties = array('pid', 'cmd_put', 'cmd_stats_job');
        foreach ($properties as $property) {
            $this->assertTrue(
                isset($stats->$property),
                "property $property should exist"
            );
        }

        $this->assertTrue($stats->pid > 0, 'stats should have pid > 0');
        $this->assertTrue($stats->cmd_use > 0, 'stats should have cmd_use > 0');
    }

    public function testPauseTube()
    {
        $tube = 'test-pause-tube';
        $pheanstalk = $this->_getFacade();

        $pheanstalk
            ->useTube($tube)
            ->watch($tube)
            ->ignore('default')
            ->put(__METHOD__);

        $response = $pheanstalk
            ->pauseTube($tube, 1)
            ->reserve(0);

        $this->assertSame($response, false);
    }

    public function testGetConnection()
    {
        $facade = $this->_getFacade();
        
        $connection = $this->getMockBuilder('Pheanstalk_Connection')
            ->disableOriginalConstructor()
            ->getMock();
        
        $facade->setConnection($connection);
        $this->assertSame($facade->getConnection(), $connection);
    }
    
    public function testInterface()
    {
        $facade = $this->_getFacade();
        
        $this->assertInstanceOf('Pheanstalk_PheanstalkInterface', $facade);
    }

    // ----------------------------------------
    // private

    private function _getFacade()
    {
        return new Pheanstalk_Pheanstalk(self::SERVER_HOST);
    }
}

