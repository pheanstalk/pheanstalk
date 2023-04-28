<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration;

use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\PheanstalkPublisherInterface;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\Exception\JobTooBigException;
use Pheanstalk\Exception\NotIgnoredException;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\Job;
use Pheanstalk\Values\JobState;
use Pheanstalk\Values\TubeName;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Pheanstalk\Pheanstalk
 * @covers \Pheanstalk\Values\ResponseType
 * @covers \Pheanstalk\PheanstalkSubscriber
 * @covers \Pheanstalk\PheanstalkManager
 * @covers \Pheanstalk\PheanstalkPublisher
 */
abstract class PheanstalkTestBase extends TestCase
{
    use BugfixConnectionTests;
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

    /**
     * @return iterable<list<string>>
     */
    public static function tubeNameProvider(): iterable
    {
        yield ['random$test'];
        yield ['110234'];
        yield ['0.5'];
    }

    /**
     * @dataProvider tubeNameProvider
     */
    public function testUseTube(string $name): void
    {
        $pheanstalk = $this->getPheanstalk();

        $pheanstalk->useTube(new TubeName($name));
        Assert::assertSame($name, $pheanstalk->listTubeUsed()->value);
        $jobId = $pheanstalk->put("testdata");
        Assert::assertSame($name, $pheanstalk->statsJob($jobId)->tube->value);
    }

    public function testListTubesWatched(): void
    {
        $pheanstalk = $this->getPheanstalk();

        Assert::assertEquals([new TubeName('default')], iterator_to_array($pheanstalk->listTubesWatched()));

        $pheanstalk->watch(new TubeName('test'));
        Assert::assertEquals([new TubeName('default'), new TubeName('test')], iterator_to_array($pheanstalk->listTubesWatched()));

        $pheanstalk->ignore(new TubeName('default'));
        Assert::assertEquals([new TubeName('test')], iterator_to_array($pheanstalk->listTubesWatched()));
    }

    public function testIgnoreLastTube(): void
    {
        $this->expectException(NotIgnoredException::class);
        $pheanstalk = $this->getPheanstalk();

        $pheanstalk->ignore(new TubeName('default'));
    }

    public function testPutReserveAndDeleteData(): void
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

    /**
     * @covers \Pheanstalk\Pheanstalk::release
     */
    public function testRelease(): void
    {
        $pheanstalk = $this->getPheanstalk();

        $jobId = $pheanstalk->put(__METHOD__);

        $pheanstalk->reserveJob($jobId);
        Assert::assertSame(JobState::RESERVED, $pheanstalk->statsJob($jobId)->state);

        $pheanstalk->release($jobId, 12333, 411);
        $jobStats = $pheanstalk->statsJob($jobId);
        Assert::assertSame(JobState::DELAYED, $jobStats->state);
        Assert::assertSame(12333, $jobStats->priority);
        Assert::assertSame(411, $jobStats->delay);

        $pheanstalk->reserveJob($jobId);
        Assert::assertSame(JobState::RESERVED, $pheanstalk->statsJob($jobId)->state);

        $pheanstalk->release($jobId);
        $jobStats = $pheanstalk->statsJob($jobId);
        Assert::assertSame(JobState::READY, $jobStats->state, "Got state {$jobStats->state->name}");
        Assert::assertSame(0, $jobStats->delay);
        Assert::assertSame(PheanstalkPublisherInterface::DEFAULT_PRIORITY, $jobStats->priority);
    }

    public function testPut(): void
    {
        $pheanstalk = $this->getPheanstalk();

        $jobId = $pheanstalk->put('abc');

        self::assertSame('abc', $pheanstalk->peek($jobId)->getData());
    }

    public function testPutJobTooBig(): void
    {
        $this->expectException(JobTooBigException::class);
        $pheanstalk = $this->getPheanstalk();

        $pheanstalk->put(str_repeat('0', 0x10000));
    }

    public function testTouch(): void
    {
        $pheanstalk = $this->getPheanstalk();

        $pheanstalk->put(__METHOD__, 1, 0, 10);
        $job = $pheanstalk->reserve();
        Assert::assertEquals(9, $pheanstalk->statsJob($job)->timeLeft);
        sleep(1);
        Assert::assertEquals(8, $pheanstalk->statsJob($job)->timeLeft);
        $pheanstalk->touch($job);
        Assert::assertEquals(9, $pheanstalk->statsJob($job)->timeLeft);
    }

    public function testListTubes(): void
    {
        $pheanstalk = $this->getPheanstalk();

        Assert::assertEquals([new TubeName('default')], iterator_to_array($pheanstalk->listTubes()));

        $pheanstalk->useTube(new TubeName('test1'));
        Assert::assertEquals([new TubeName('default'), new TubeName('test1')], iterator_to_array($pheanstalk->listTubes()));

        $pheanstalk->watch(new TubeName('test2'));
        Assert::assertEquals([new TubeName('default'), new TubeName('test1'), new TubeName('test2')], iterator_to_array($pheanstalk->listTubes()));
    }

    public function testPeek(): void
    {
        $pheanstalk = $this->getPheanstalk();
        $jobId1 = $pheanstalk->put('test');

        Assert::assertSame('test', $pheanstalk->peek($jobId1)->getData());

        // put job in an unused tube
        $pheanstalk->useTube(new TubeName('testpeek2'));
        $jobId2 = $pheanstalk->put('test2');
        $pheanstalk->useTube(new TubeName('default'));

        Assert::assertSame('test2', $pheanstalk->peek($jobId2)->getData());
    }

    public function testPeekReady(): void
    {
        $pheanstalk = $this->getPheanstalk();
        $pheanstalk->useTube(new TubeName('testpeekready'));
        $jobId = $pheanstalk->put('test');

        $peekedJob = $pheanstalk->peekReady();
        Assert::assertInstanceOf(Job::class, $peekedJob);
        Assert::assertSame($jobId->getId(), $peekedJob->getId());
    }

    public function testPeekDelayed(): void
    {
        $pheanstalk = $this->getPheanstalk();
        $pheanstalk->useTube(new TubeName('testpeekdelayed'));
        $jobId = $pheanstalk->put('test', 0, 2);

        $peekedJob = $pheanstalk->peekDelayed();
        Assert::assertInstanceOf(Job::class, $peekedJob);
        Assert::assertSame($jobId->getId(), $peekedJob->getId());
    }

    public function testPeekBuried(): void
    {
        $pheanstalk = $this->getPheanstalk();
        $jobId = $pheanstalk->put('test', 0);

        $pheanstalk->reserveJob($jobId);
        $pheanstalk->bury($jobId);
        $peekedJob = $pheanstalk->peekBuried();
        Assert::assertInstanceOf(Job::class, $peekedJob);
        Assert::assertSame($jobId->getId(), $peekedJob->getId());
    }

    public function testStatsJob(): void
    {
        $pheanstalk = $this->getPheanstalk();
        $pheanstalk->useTube(new TubeName('teststatsjob'));
        $jobId = $pheanstalk->put('test');

        $stats = $pheanstalk->statsJob($jobId);

        Assert::assertSame($jobId->getId(), $stats->id->getId());
        Assert::assertSame('teststatsjob', $stats->tube->value);
        Assert::assertEquals(JobState::READY, $stats->state);
        Assert::assertEquals(PheanstalkPublisherInterface::DEFAULT_PRIORITY, $stats->priority);
        Assert::assertEquals(PheanstalkPublisherInterface::DEFAULT_DELAY, $stats->delay);
        Assert::assertEquals(PheanstalkPublisherInterface::DEFAULT_TTR, $stats->timeToRelease);
        Assert::assertEquals(0, $stats->timeouts);
        Assert::assertEquals(0, $stats->releases);
        Assert::assertEquals(0, $stats->buries);
        Assert::assertEquals(0, $stats->kicks);
    }

    public function testStatsTube(): void
    {
        $pheanstalk = $this->getPheanstalk();

        $tube = new TubeName('test-stats-tube');

        $pheanstalk->useTube($tube);

        $stats = $pheanstalk->statsTube($tube);

        Assert::assertEquals($tube->value, $stats->name);
        Assert::assertEquals('0', $stats->currentJobsReserved);
    }

    public function testStatsServer(): void
    {
        $pheanstalk = $this->getPheanstalk();
        $pheanstalk->useTube(new TubeName('test-stats'));
        $stats = $pheanstalk->stats();

        self::assertTrue($stats->pid > 0, 'stats should have pid > 0');
        self::assertTrue($stats->cmdUse > 0, 'stats should have cmd_use > 0');
    }

    public function testPauseTube(): void
    {
        $tube = new TubeName('test-pause-tube');
        $pheanstalk = $this->getPheanstalk();

        $pheanstalk->useTube($tube);
        $pheanstalk->watch($tube);
        $pheanstalk->ignore(new TubeName('default'));
        $pheanstalk->put(__METHOD__);

        // pause, expect no job from that queue
        $pheanstalk->pauseTube($tube, 60);
        $response = $pheanstalk->reserveWithTimeout(0);

        Assert::assertNull($response);

        // resume, expect job
        $pheanstalk->resumeTube($tube);
        $response = $pheanstalk->reserveWithTimeout(0);

        Assert::assertInstanceOf(Job::class, $response);
        Assert::assertSame($response->getData(), __METHOD__);
    }

    /**
     * @covers \Pheanstalk\Pheanstalk::kickJob
     */
    public function testKickJob(): void
    {
        $pheanstalk = $this->getPheanstalk();
        $jobId = $pheanstalk->put('abc');
        $pheanstalk->reserveJob($jobId);
        $pheanstalk->bury($jobId);

        self::assertSame(JobState::BURIED, $pheanstalk->statsJob($jobId)->state);
        $pheanstalk->kickJob($jobId);
        self::assertSame(JobState::READY, $pheanstalk->statsJob($jobId)->state);
    }

    public function testDeadlineSoonDuringReserveWithTimeout(): void
    {
        $pheanstalk = $this->getPheanstalk();
        $jobId = $pheanstalk->put('abc', 1, 0, 1);
        $pheanstalk->reserveJob($jobId);
        $this->expectException(DeadlineSoonException::class);
        $pheanstalk->reserveWithTimeout(3);
    }

    public function testDeadlineSoonDuringReserve(): void
    {
        $pheanstalk = $this->getPheanstalk();
        $jobId = $pheanstalk->put('abc', 1, 0, 1);
        $pheanstalk->reserveJob($jobId);
        $this->expectException(DeadlineSoonException::class);
        $pheanstalk->reserve();
    }


    public function testNoDeadlineSoonDuringReserveJob(): void
    {
        $pheanstalk = $this->getPheanstalk();
        $jobId = $pheanstalk->put('abc', 1, 0, 1);
        $jobId2 = $pheanstalk->put('abc', 1, 0, 1);
        $pheanstalk->reserveJob($jobId);
        // We keep reserving and releasing job2 until the first job is auto released by the server.
        while ($pheanstalk->statsJob($jobId)->state === JobState::RESERVED) {
            $pheanstalk->reserveJob($jobId2);
            $pheanstalk->release($jobId2);
            usleep(10000);
        }
        self::assertSame(JobState::READY, $pheanstalk->statsJob($jobId)->state);
    }

    public function testNoDeadlineSoonDuringDeleteJob(): void
    {
        $pheanstalk = $this->getPheanstalk();
        $jobId = $pheanstalk->put('abc', 1, 0, 1);
        $ids = [];
        for ($i = 0; $i < 100; $i++) {
            $ids[] = $pheanstalk->put("job$i");
        }
        $pheanstalk->reserveJob($jobId);
        while ($pheanstalk->statsJob($jobId)->state === JobState::RESERVED) {
            $id = array_shift($ids);
            Assert::assertInstanceOf(JobIdInterface::class, $id);
            $pheanstalk->delete($id);
            usleep(100000);
        }

        self::assertLessThan(91, count($ids));
    }

    public function testNoDeadlineSoonDuringDeleteReservedJob(): void
    {
        $pheanstalk = $this->getPheanstalk();
        $jobId = $pheanstalk->put('abc', 1, 0, 1);
        $pheanstalk->reserveJob($jobId);
        usleep(100000);
        $pheanstalk->delete($jobId);

        $this->expectException(JobNotFoundException::class);
        $pheanstalk->statsJob($jobId);
    }

    public function testKickKicksDelayedJobWhenNoJobsAreBuried(): void
    {
        $pheanstalk = $this->getPheanstalk();
        self::assertSame(0, $pheanstalk->stats()->currentJobsBuried);
        $jobId = $pheanstalk->put('abc', 1, 20, 1);
        self::assertSame(JobState::DELAYED, $pheanstalk->statsJob($jobId)->state);
        self::assertSame(1, $pheanstalk->kick(1));
        self::assertSame(JobState::READY, $pheanstalk->statsJob($jobId)->state);
    }

    final protected function getHost(): string
    {
        if (str_contains(static::class, "Unix")) {
            if (UNIX_SERVER_HOST === '') {
                self::markTestSkipped('No Unix socket configured via UNIX_SERVER_HOST');
            }
            return UNIX_SERVER_HOST;
        } elseif (SERVER_HOST === '') {
            self::markTestSkipped('No server host configured via SERVER_HOST');
        }
        return SERVER_HOST;
    }
    abstract protected function getPheanstalk(): Pheanstalk;
}
