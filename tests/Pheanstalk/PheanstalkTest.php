<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\SocketException;
use Pheanstalk\Socket\FsockopenSocket;
use PHPUnit\Framework\Assert;
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

        Assert::assertEquals('default', $pheanstalk->listTubeUsed());
        Assert::assertEquals('default', $pheanstalk->listTubeUsed(true));

        $pheanstalk->useTube('test');
        Assert::assertEquals('test', $pheanstalk->listTubeUsed());
        Assert::assertEquals('test', $pheanstalk->listTubeUsed(true));
    }

    public function testWatchlist()
    {
        $pheanstalk = $this->getPheanstalk();

        Assert::assertEquals($pheanstalk->listTubesWatched(), ['default']);
        Assert::assertEquals($pheanstalk->listTubesWatched(true), ['default']);

        $pheanstalk->watch('test');
        Assert::assertEquals($pheanstalk->listTubesWatched(), ['default', 'test']);
        Assert::assertEquals($pheanstalk->listTubesWatched(true), ['default', 'test']);

        $pheanstalk->ignore('default');
        Assert::assertEquals($pheanstalk->listTubesWatched(), ['test']);
        Assert::assertEquals($pheanstalk->listTubesWatched(true), ['test']);

        $pheanstalk->watchOnly('default');
        Assert::assertEquals($pheanstalk->listTubesWatched(), ['default']);
        Assert::assertEquals($pheanstalk->listTubesWatched(true), ['default']);
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
        \PHPUnit\Framework\Assert::assertNotNull($job);
        Assert::assertEquals($putJob->getId(), $job->getId());


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
        \PHPUnit\Framework\Assert::assertNotNull($job);
        Assert::assertEquals($putJob->getId(), $job->getId());
        // delete the reserved job
        $pheanstalk->delete($job);
    }

    public function testRelease()
    {
        $pheanstalk1 = $this->getPheanstalk();
        $pheanstalk2 = $this->getPheanstalk();

        $pheanstalk1->put(__METHOD__);
        $job = $pheanstalk1->reserve();

        \PHPUnit\Framework\Assert::assertNull($pheanstalk2->reserveWithTimeout(0));
        $pheanstalk1->release($job);
        \PHPUnit\Framework\Assert::assertNotNull($pheanstalk2->reserveWithTimeout(0));
    }

    public function testReleaseWithDelay()
    {
        $pheanstalk1 = $this->getPheanstalk();
        $pheanstalk2 = $this->getPheanstalk();

        $pheanstalk1->put(__METHOD__);
        $job = $pheanstalk1->reserve();

        \PHPUnit\Framework\Assert::assertNull($pheanstalk2->reserveWithTimeout(0));
        $pheanstalk1->release($job, 1, 1);
        \PHPUnit\Framework\Assert::assertNull($pheanstalk2->reserveWithTimeout(0));
        sleep(2);
        \PHPUnit\Framework\Assert::assertNotNull($pheanstalk2->reserveWithTimeout(0));
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

        Assert::assertEquals(
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
        Assert::assertEquals(9, $pheanstalk->statsJob($job)['time-left']);
        sleep(1);
        Assert::assertEquals(8, $pheanstalk->statsJob($job)['time-left']);
        $pheanstalk->touch($job);
        Assert::assertEquals(9, $pheanstalk->statsJob($job)['time-left']);
    }

    public function testListTubes()
    {
        $pheanstalk = $this->getPheanstalk();

        Assert::assertTrue(in_array('default', $pheanstalk->listTubes(), true));

        $pheanstalk->useTube('test1');
        Assert::assertTrue(in_array('test1', $pheanstalk->listTubes(), true));

        $pheanstalk->watch('test2');
        Assert::assertTrue(in_array('test2', $pheanstalk->listTubes(), true));
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

        Assert::assertEquals($job->getData(), 'test');

        // put job in an unused tube
        $putJob = $pheanstalk->withUsedTube('testpeek2', function ($pheanstalk) {
            return $pheanstalk->put('test2');
        });

        $job = $pheanstalk->peek($putJob);

        Assert::assertEquals($job->getData(), 'test2');
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

        Assert::assertEquals($job->getData(), 'test');
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

        Assert::assertEquals($job->getData(), 'test');
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

        Assert::assertEquals($job->getData(), 'test');
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

        Assert::assertEquals($putJob->getId(), $stats->id);
        Assert::assertEquals('teststatsjob', $stats->tube);
        Assert::assertEquals('ready', $stats->state);
        Assert::assertEquals(Pheanstalk::DEFAULT_PRIORITY, $stats->pri);
        Assert::assertEquals(Pheanstalk::DEFAULT_DELAY, $stats->delay);
        Assert::assertEquals(Pheanstalk::DEFAULT_TTR, $stats->ttr);
        Assert::assertEquals(0, $stats->timeouts);
        Assert::assertEquals(0, $stats->releases);
        Assert::assertEquals(0, $stats->buries);
        Assert::assertEquals(0, $stats->kicks);
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

        Assert::assertEquals($stats->id, $job->getId());
        Assert::assertEquals($stats->tube, 'teststatsjobwithjobobject');
        Assert::assertEquals($stats->state, 'reserved');
        Assert::assertEquals($stats->pri, Pheanstalk::DEFAULT_PRIORITY);
        Assert::assertEquals($stats->delay, Pheanstalk::DEFAULT_DELAY);
        Assert::assertEquals($stats->ttr, Pheanstalk::DEFAULT_TTR);
        Assert::assertEquals($stats->timeouts, 0);
        Assert::assertEquals($stats->releases, 0);
        Assert::assertEquals($stats->buries, 0);
        Assert::assertEquals($stats->kicks, 0);
    }

    public function testStatsTube()
    {
        $pheanstalk = $this->getPheanstalk();

        $tube = 'test-stats-tube';
        $pheanstalk->useTube($tube);

        $stats = $pheanstalk->statsTube($tube);

        Assert::assertEquals($stats->name, $tube);
        Assert::assertEquals($stats->current_jobs_reserved, '0');
    }

    public function testStats()
    {
        $pheanstalk = $this->getPheanstalk();

        $stats = $pheanstalk->useTube('test-stats')->stats();

        $properties = ['pid', 'cmd_put', 'cmd_stats_job'];
        foreach ($properties as $property) {
            Assert::assertTrue(
                /** @phpstan-ignore-next-line */
                isset($stats->$property),
                "property $property should exist"
            );
        }

        Assert::assertTrue($stats->pid > 0, 'stats should have pid > 0');
        Assert::assertTrue($stats->cmd_use > 0, 'stats should have cmd_use > 0');
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

        \PHPUnit\Framework\Assert::assertNull($response);

        // resume, expect job
        $pheanstalk->resumeTube($tube);
        $response = $pheanstalk->reserveWithTimeout(0);

        \PHPUnit\Framework\Assert::assertSame($response->getData(), __METHOD__);
    }

    public function testConnectionResetIfSocketExceptionIsThrown()
    {
        $sockets = [];

        $sockets[0] = $this->getMockBuilder(SocketInterface::class)
            ->getMock();

        $sockets[1] = new FsockopenSocket(SERVER_HOST, 11300, 10);

        $sockets[0]->expects(self::once())->method('write')->willThrowException(new SocketException('test'));

        $socketFactory = new class($sockets) implements SocketFactoryInterface {
            /**
             * @param list<SocketInterface> $sockets
             */
            public function __construct(private array $sockets)
            {
            }

            public function create(): SocketInterface
            {
                /** @phpstan-ignore-next-line */
                return array_shift($this->sockets);
            }
        };

        $pheanstalk = Pheanstalk::createWithFactory($socketFactory);
        \PHPUnit\Framework\Assert::assertNotEmpty($pheanstalk->stats());
    }

    // ----------------------------------------
    // private

    private function getPheanstalk(): Pheanstalk
    {
        return Pheanstalk::create(SERVER_HOST);
    }
}
