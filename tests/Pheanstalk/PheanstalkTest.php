<?php

namespace Pheanstalk;

use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\SocketException;
use Pheanstalk\Job;
use Pheanstalk\Socket\FsockopenSocket;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Pheanstalk facade (the base class).
 * Relies on a running beanstalkd server.
 *
 * @author  Paul Annesley
 */
class PheanstalkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Drain
        $pheanstalk = $this->getPheanstalk();
        foreach ($pheanstalk->listTubes() as $tube) {
            $pheanstalk->useTube($tube);
            while (null !== $job = $pheanstalk->peekReady()) {
                $pheanstalk->delete($job);
            }
            while (null !== $job = $pheanstalk->peekBuried()) {
                $pheanstalk->delete($job);
            }
            while (null !== $job = $pheanstalk->peekDelayed()) {
                $pheanstalk->delete($job);
            }
        }
    }


    public function testUseTube()
    {
        $pheanstalk = $this->getPheanstalk();

        $this->assertEquals('default', $pheanstalk->listTubeUsed());
        $this->assertEquals('default', $pheanstalk->listTubeUsed(true));

        $pheanstalk->useTube('test');
        $this->assertEquals('test', $pheanstalk->listTubeUsed());
        $this->assertEquals('test', $pheanstalk->listTubeUsed(true));
    }

    public function testWatchlist()
    {
        $pheanstalk = $this->getPheanstalk();

        $this->assertEquals($pheanstalk->listTubesWatched(), ['default']);
        $this->assertEquals($pheanstalk->listTubesWatched(true), ['default']);

        $pheanstalk->watch('test');
        $this->assertEquals($pheanstalk->listTubesWatched(), ['default', 'test']);
        $this->assertEquals($pheanstalk->listTubesWatched(true), ['default', 'test']);

        $pheanstalk->ignore('default');
        $this->assertEquals($pheanstalk->listTubesWatched(), ['test']);
        $this->assertEquals($pheanstalk->listTubesWatched(true), ['test']);

        $pheanstalk->watchOnly('default');
        $this->assertEquals($pheanstalk->listTubesWatched(), ['default']);
        $this->assertEquals($pheanstalk->listTubesWatched(true), ['default']);
    }

    public function testIgnoreLastTube()
    {
        $this->expectException(Exception::class);
        $pheanstalk = $this->getPheanstalk();

        $pheanstalk->ignore('default');
    }

    public function testPutReserveAndDeleteData()
    {
        /** @var Pheanstalk $pheanstalk */
        $pheanstalk = $this->getPheanstalk();

        $putJob = $pheanstalk->put(__METHOD__);

        // reserve a job - can't assume it is the one just added
        $job = $pheanstalk->reserveWithTimeout(0);
        $this->assertNotNull($job);
        $this->assertEquals($putJob->getId(), $job->getId());


        // delete the reserved job
        $pheanstalk->delete($job);

        // put a job into an unused tube
        $putJob = $pheanstalk->withUsedTube('test', function (Pheanstalk $pheanstalk) {
            return $pheanstalk->put(__METHOD__);
        });


        // reserve a job from an unwatched tube - can't assume it is the one just added
        $job = $pheanstalk->withWatchedTube('test', function (Pheanstalk $ph) {
            return $ph->reserveWithTimeout(0);
        });
        $this->assertNotNull($job);
        $this->assertEquals($putJob->getId(), $job->getId());
        // delete the reserved job
        $pheanstalk->delete($job);
    }

    public function testRelease()
    {
        $pheanstalk1 = $this->getPheanstalk();
        $pheanstalk2 = $this->getPheanstalk();

        $pheanstalk1->put(__METHOD__);
        $job = $pheanstalk1->reserve();

        $this->assertNull($pheanstalk2->reserveWithTimeout(0));
        $pheanstalk1->release($job);
        $this->assertNotNull($pheanstalk2->reserveWithTimeout(0));
    }

    public function testReleaseWithDelay()
    {
        $pheanstalk1 = $this->getPheanstalk();
        $pheanstalk2 = $this->getPheanstalk();

        $pheanstalk1->put(__METHOD__);
        $job = $pheanstalk1->reserve();

        $this->assertNull($pheanstalk2->reserveWithTimeout(0));
        $pheanstalk1->release($job, 1, 1);
        $this->assertNull($pheanstalk2->reserveWithTimeout(0));
        sleep(2);
        $this->assertNotNull($pheanstalk2->reserveWithTimeout(0));
    }


    public function testPutBuryAndKick()
    {
        $pheanstalk = $this->getPheanstalk();

        $putJob = $pheanstalk->put(__METHOD__);

        // reserve a job - can't assume it is the one just added
        $job = $pheanstalk->reserve();

        // bury the reserved job
        $pheanstalk->bury($job);

        // kick up to one job
        $kickedCount = $pheanstalk->kick(1);

        $this->assertEquals(
            $kickedCount,
            1,
            'there should be at least one buried (or delayed) job: %s'
        );
    }

    public function testPutJobTooBig()
    {
        $this->expectException(Exception::class);
        $pheanstalk = $this->getPheanstalk();

        $pheanstalk->put(str_repeat('0', 0x10000));
    }

    public function testTouch()
    {
        $pheanstalk = $this->getPheanstalk();

        $pheanstalk->put(__METHOD__, 1, 0, 10);
        $job = $pheanstalk->reserve();
        $this->assertEquals(9, $pheanstalk->statsJob($job)['time-left']);
        sleep(1);
        $this->assertEquals(8, $pheanstalk->statsJob($job)['time-left']);
        $pheanstalk->touch($job);
        $this->assertEquals(9, $pheanstalk->statsJob($job)['time-left']);
    }

    public function testListTubes()
    {
        $pheanstalk = $this->getPheanstalk();

        $this->assertTrue(in_array('default', $pheanstalk->listTubes()));

        $pheanstalk->useTube('test1');
        $this->assertTrue(in_array('test1', $pheanstalk->listTubes()));

        $pheanstalk->watch('test2');
        $this->assertTrue(in_array('test2', $pheanstalk->listTubes()));
    }

    public function testPeek()
    {
        $pheanstalk = $this->getPheanstalk();

        $putJob = $pheanstalk
            ->useTube('testpeek')
            ->watch('testpeek')
            ->ignore('default')
            ->put('test');

        $job = $pheanstalk->peek($putJob);

        $this->assertEquals($job->getData(), 'test');

        // put job in an unused tube
        $putJob = $pheanstalk->withUsedTube('testpeek2', function ($pheanstalk) {
            return $pheanstalk->put('test2');
        });

        $job = $pheanstalk->peek($putJob);

        $this->assertEquals($job->getData(), 'test2');
    }

    public function testPeekReady()
    {
        $pheanstalk = $this->getPheanstalk();

        $id = $pheanstalk
            ->useTube('testpeekready')
            ->watch('testpeekready')
            ->ignore('default')
            ->put('test');

        $job = $pheanstalk->peekReady();

        $this->assertEquals($job->getData(), 'test');
    }

    public function testPeekDelayed()
    {
        $pheanstalk = $this->getPheanstalk();

        $id = $pheanstalk
            ->useTube('testpeekdelayed')
            ->watch('testpeekdelayed')
            ->ignore('default')
            ->put('test', 0, 2);

        $job = $pheanstalk->peekDelayed();

        $this->assertEquals($job->getData(), 'test');
    }

    public function testPeekBuried()
    {
        $pheanstalk = $this->getPheanstalk();

        $putJob = $pheanstalk
            ->useTube('testpeekburied')
            ->watch('testpeekburied')
            ->ignore('default')
            ->put('test');

        $job = $pheanstalk->reserve();
        $pheanstalk->bury($job);

        $job = $pheanstalk->peekBuried();

        $this->assertEquals($job->getData(), 'test');
    }

    public function testStatsJob()
    {
        $pheanstalk = $this->getPheanstalk();

        $putJob = $pheanstalk
            ->useTube('teststatsjob')
            ->watch('teststatsjob')
            ->ignore('default')
            ->put('test');

        $stats = $pheanstalk->statsJob($putJob);

        $this->assertEquals($stats->id, $putJob->getId());
        $this->assertEquals($stats->tube, 'teststatsjob');
        $this->assertEquals($stats->state, 'ready');
        $this->assertEquals($stats->pri, Pheanstalk::DEFAULT_PRIORITY);
        $this->assertEquals($stats->delay, Pheanstalk::DEFAULT_DELAY);
        $this->assertEquals($stats->ttr, Pheanstalk::DEFAULT_TTR);
        $this->assertEquals($stats->timeouts, 0);
        $this->assertEquals($stats->releases, 0);
        $this->assertEquals($stats->buries, 0);
        $this->assertEquals($stats->kicks, 0);
    }

    public function testStatsJobWithJobObject()
    {
        $pheanstalk = $this->getPheanstalk();

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
        $this->assertEquals($stats->pri, Pheanstalk::DEFAULT_PRIORITY);
        $this->assertEquals($stats->delay, Pheanstalk::DEFAULT_DELAY);
        $this->assertEquals($stats->ttr, Pheanstalk::DEFAULT_TTR);
        $this->assertEquals($stats->timeouts, 0);
        $this->assertEquals($stats->releases, 0);
        $this->assertEquals($stats->buries, 0);
        $this->assertEquals($stats->kicks, 0);
    }

    public function testStatsTube()
    {
        $pheanstalk = $this->getPheanstalk();

        $tube = 'test-stats-tube';
        $pheanstalk->useTube($tube);

        $stats = $pheanstalk->statsTube($tube);

        $this->assertEquals($stats->name, $tube);
        $this->assertEquals($stats->current_jobs_reserved, '0');
    }

    public function testStats()
    {
        $pheanstalk = $this->getPheanstalk();

        $stats = $pheanstalk->useTube('test-stats')->stats();

        $properties = ['pid', 'cmd_put', 'cmd_stats_job'];
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
        $pheanstalk = $this->getPheanstalk();

        $pheanstalk
            ->useTube($tube)
            ->watch($tube)
            ->ignore('default')
            ->put(__METHOD__);

        // pause, expect no job from that queue
        $pheanstalk->pauseTube($tube, 60);
        $response = $pheanstalk->reserveWithTimeout(0);

        $this->assertNull($response);

        // resume, expect job
        $pheanstalk->resumeTube($tube);
        $response = $pheanstalk->reserveWithTimeout(0);

        $this->assertSame($response->getData(), __METHOD__);
    }

    public function testInterface()
    {
        $facade = $this->getPheanstalk();

        $this->assertInstanceOf(PheanstalkInterface::class, $facade);
    }


    public function testConnectionResetIfSocketExceptionIsThrown()
    {
        $sockets = [];

        $sockets[0] = $this->getMockBuilder(SocketInterface::class)
            ->getMock();

        $sockets[1] = new FsockopenSocket(SERVER_HOST, 11300, 10);

        $sockets[0]->expects($this->once())->method('write')->willThrowException(new SocketException('test'));

        $socketFactory = new class($sockets) implements SocketFactoryInterface {
            private $sockets;
            public function __construct(array $sockets)
            {
                $this->sockets = $sockets;
            }

            public function create(): SocketInterface
            {
                return array_shift($this->sockets);
            }
        };

        $pheanstalk = Pheanstalk::createWithFactory($socketFactory);
        $this->assertNotEmpty($pheanstalk->stats());
    }

    // ----------------------------------------
    // private

    private function getPheanstalk(): PheanstalkInterface
    {
        return Pheanstalk::create(SERVER_HOST);
    }
}
