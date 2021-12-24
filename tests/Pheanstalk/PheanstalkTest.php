<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\SocketException;
use Pheanstalk\Socket\FsockopenSocket;
use phpDocumentor\Reflection\Types\Void_;
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
    }

    public function testWatchlist()
    {
        $pheanstalk = $this->getPheanstalk();

        Assert::assertSame(['default'], $pheanstalk->listTubesWatched());

        $pheanstalk->watch('test');
        Assert::assertEquals(['default', 'test'], $pheanstalk->listTubesWatched());

        $pheanstalk->ignore('default');
        Assert::assertEquals(['test'], $pheanstalk->listTubesWatched());

    }

    public function testIgnoreLastTube()
    {
        $this->expectException(Exception::class);
        $pheanstalk = $this->getPheanstalk();

        $pheanstalk->ignore('default');
    }

    public function testPutReserveAndDeleteData()
    {
        $pheanstalk = $this->getPheanstalk();

        $putJob = $pheanstalk->put(__METHOD__);

        // reserve a job - can't assume it is the one just added
        $job = $pheanstalk->reserveWithTimeout(0);
        Assert::assertNotNull($job);
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

        Assert::assertNull($pheanstalk2->reserveWithTimeout(0));
        $pheanstalk1->release($job);
        Assert::assertNotNull($pheanstalk2->reserveWithTimeout(0));
    }

    public function testReleaseWithDelay()
    {
        $pheanstalk1 = $this->getPheanstalk();
        $pheanstalk2 = $this->getPheanstalk();

        $pheanstalk1->put(__METHOD__);
        $job = $pheanstalk1->reserve();

        Assert::assertNull($pheanstalk2->reserveWithTimeout(0));
        $pheanstalk1->release($job, 1, 1);
        Assert::assertNull($pheanstalk2->reserveWithTimeout(0));
        sleep(2);
        Assert::assertNotNull($pheanstalk2->reserveWithTimeout(0));
    }

    /**
     * @depends testStats
     */
    public function testPut(): void
    {
        $pheanstalk = $this->getPheanstalk();

        $current = (int)$pheanstalk->stats()['current-jobs-ready'];

        $pheanstalk->put('abc');
        Assert::assertSame($current + 1, (int)$pheanstalk->stats()['current-jobs-ready']);

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

        $pheanstalk->useTube('testpeek');
        $pheanstalk->watch('testpeek');
        $pheanstalk->ignore('default');
        $putJob = $pheanstalk->put('test');

        $job = $pheanstalk->peek($putJob);

        Assert::assertEquals('test', $job->getData());

        // put job in an unused tube
        $pheanstalk->useTube('testpeek2');
        $putJob2 =  $pheanstalk->put('test2');

        $job = $pheanstalk->peek($putJob2);

        Assert::assertEquals('test2', $job->getData());
    }

    public function testPeekReady()
    {
        $pheanstalk = $this->getPheanstalk();
        $pheanstalk->useTube('testpeekready');
        $job = $pheanstalk->put('test');

        $peekedJob = $pheanstalk->peekReady();

        Assert::assertSame($job->getId(), $peekedJob->getId());
        Assert::assertSame('test', $peekedJob->getData());
    }

    public function testPeekDelayed()
    {
        $pheanstalk = $this->getPheanstalk();
        $pheanstalk->useTube('testpeekdelayed');
        $job = $pheanstalk->put('test', 0, 2);

        $peekedJob = $pheanstalk->peekDelayed();

        Assert::assertSame($job->getId(), $peekedJob->getId());
        Assert::assertSame('test', $peekedJob->getData());
    }

    public function testPeekBuried():void
    {
        $pheanstalk = $this->getPheanstalk();
        $job = $pheanstalk->put('test', 0);

        $pheanstalk->reserveJob($job);
        $pheanstalk->bury($job);
        $peekedJob = $pheanstalk->peekBuried();

        Assert::assertSame($job->getId(), $peekedJob->getId());
        Assert::assertSame('test', $peekedJob->getData());
    }

    public function testStatsJob()
    {
        $pheanstalk = $this->getPheanstalk();

        $pheanstalk->useTube('teststatsjob');

        $putJob = $pheanstalk->put('test');

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

    public function testStatsTube(): void
    {
        $pheanstalk = $this->getPheanstalk();

        $tube = 'test-stats-tube';
        $pheanstalk->useTube($tube);

        $stats = $pheanstalk->statsTube($tube);

        Assert::assertEquals($tube, $stats->name, );
        Assert::assertEquals('0', $stats->current_jobs_reserved);
    }

    public function testStats(): void
    {
        $pheanstalk = $this->getPheanstalk();
        $pheanstalk->useTube('test-stats');
        $stats = $pheanstalk->stats();

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

        $pheanstalk->useTube($tube);
        $pheanstalk->watch($tube);
        $pheanstalk->ignore('default');
        $pheanstalk->put(__METHOD__);

        // pause, expect no job from that queue
        $pheanstalk->pauseTube($tube, 60);
        $response = $pheanstalk->reserveWithTimeout(0);

        Assert::assertNull($response);

        // resume, expect job
        $pheanstalk->resumeTube($tube);
        $response = $pheanstalk->reserveWithTimeout(0);

        Assert::assertSame($response->getData(), __METHOD__);
    }

    // ----------------------------------------
    // private

    private function getPheanstalk(): Pheanstalk
    {
        return Pheanstalk::create(SERVER_HOST);
    }
}
