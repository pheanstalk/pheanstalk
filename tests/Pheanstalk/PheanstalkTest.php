<?php

namespace Pheanstalk\Tests;

use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception;
use Pheanstalk\Exception\SocketException;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Socket\FsockopenSocket;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Tests the Pheanstalk facade (the base class).
 * Relies on a running beanstalkd server.
 *
 * @author  Paul Annesley
 */
class PheanstalkTest extends BaseTestCase
{
    protected function setUp(): void
    {
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

        $this->assertSame('default', $pheanstalk->listTubeUsed());
        $this->assertSame('default', $pheanstalk->listTubeUsed(true));

        $pheanstalk->useTube('test');
        $this->assertSame('test', $pheanstalk->listTubeUsed());
        $this->assertSame('test', $pheanstalk->listTubeUsed(true));
    }

    public function testWatchlist()
    {
        $pheanstalk = $this->getPheanstalk();

        $this->assertSame(['default'], $pheanstalk->listTubesWatched());
        $this->assertSame(['default'], $pheanstalk->listTubesWatched(true));

        $pheanstalk->watch('test');
        $this->assertSame(['default', 'test'], $pheanstalk->listTubesWatched());
        $this->assertSame(['default', 'test'], $pheanstalk->listTubesWatched(true));

        $pheanstalk->ignore('default');
        $this->assertSame(['test'], $pheanstalk->listTubesWatched());
        $this->assertSame(['test'], $pheanstalk->listTubesWatched(true));

        $pheanstalk->watchOnly('default');
        $this->assertSame(['default'], $pheanstalk->listTubesWatched());
        $this->assertSame(['default'], $pheanstalk->listTubesWatched(true));
    }

    public function testIgnoreLastTube()
    {
        $this->expectException(Exception::class);
        $pheanstalk = $this->getPheanstalk();

        $pheanstalk->ignore('default');
    }

    public function testPutReserveAndDeleteData(): void
    {
        /** @var Pheanstalk $pheanstalk */
        $pheanstalk = $this->getPheanstalk();

        $putJob = $pheanstalk->put(__METHOD__);

        // reserve a job - can't assume it is the one just added
        $job = $pheanstalk->reserveWithTimeout(0);
        $this->assertNotNull($job);
        $this->assertSame($putJob->getId(), $job->getId());


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
        $this->assertSame($putJob->getId(), $job->getId());
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

        $this->assertSame(
            1,
            $kickedCount,
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

        $this->assertContains('default', $pheanstalk->listTubes());

        $pheanstalk->useTube('test1');
        $this->assertContains('test1', $pheanstalk->listTubes());

        $pheanstalk->watch('test2');
        $this->assertContains('test2', $pheanstalk->listTubes());
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

        $this->assertSame('test', $job->getData());

        // put job in an unused tube
        $putJob = $pheanstalk->withUsedTube('testpeek2', function ($pheanstalk) {
            return $pheanstalk->put('test2');
        });

        $job = $pheanstalk->peek($putJob);

        $this->assertSame('test2', $job->getData());
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

        $this->assertSame('test', $job->getData());
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

        $this->assertSame('test', $job->getData());
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

        $this->assertSame('test', $job->getData());
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
        $this->assertSame('teststatsjob', $stats->tube);
        $this->assertSame('ready', $stats->state);
        $this->assertEquals(Pheanstalk::DEFAULT_PRIORITY, $stats->pri);
        $this->assertEquals(Pheanstalk::DEFAULT_DELAY, $stats->delay);
        $this->assertEquals(Pheanstalk::DEFAULT_TTR, $stats->ttr);
        $this->assertEquals(0, $stats->timeouts);
        $this->assertEquals(0, $stats->releases);
        $this->assertEquals(0, $stats->buries);
        $this->assertEquals(0, $stats->kicks);
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
        $this->assertSame('teststatsjobwithjobobject', $stats->tube);
        $this->assertSame('reserved', $stats->state);
        $this->assertEquals(Pheanstalk::DEFAULT_PRIORITY, $stats->pri);
        $this->assertEquals(Pheanstalk::DEFAULT_DELAY, $stats->delay);
        $this->assertEquals(Pheanstalk::DEFAULT_TTR, $stats->ttr);
        $this->assertEquals(0, $stats->timeouts);
        $this->assertEquals(0, $stats->releases);
        $this->assertEquals(0, $stats->buries);
        $this->assertEquals(0, $stats->kicks);
    }

    public function testStatsTube()
    {
        $pheanstalk = $this->getPheanstalk();

        $tube = 'test-stats-tube';
        $pheanstalk->useTube($tube);

        $stats = $pheanstalk->statsTube($tube);

        $this->assertSame($stats->name, $tube);
        $this->assertSame('0', $stats->current_jobs_reserved);
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
