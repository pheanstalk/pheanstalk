<?php

declare(strict_types=1);

namespace Pheanstalk\Tests;

use Pheanstalk\Command\BuryCommand;
use Pheanstalk\Command\DeleteCommand;
use Pheanstalk\Command\IgnoreCommand;
use Pheanstalk\Command\KickCommand;
use Pheanstalk\Command\KickJobCommand;
use Pheanstalk\Command\ListTubesCommand;
use Pheanstalk\Command\ListTubesWatchedCommand;
use Pheanstalk\Command\ListTubeUsedCommand;
use Pheanstalk\Command\PauseTubeCommand;
use Pheanstalk\Command\PeekCommand;
use Pheanstalk\Command\PeekJobCommand;
use Pheanstalk\Command\PutCommand;
use Pheanstalk\Command\ReleaseCommand;
use Pheanstalk\Command\ReserveCommand;
use Pheanstalk\Command\ReserveJobCommand;
use Pheanstalk\Command\ReserveWithTimeoutCommand;
use Pheanstalk\Command\StatsCommand;
use Pheanstalk\Command\StatsJobCommand;
use Pheanstalk\Command\StatsTubeCommand;
use Pheanstalk\Command\TouchCommand;
use Pheanstalk\Command\UseCommand;
use Pheanstalk\Command\WatchCommand;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Exception;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\JobId;
use Pheanstalk\ResponseLine;

/**
 * Tests for Command implementations.
 *
 * @author  Paul Annesley
 */
class CommandTest extends BaseTestCase
{
    public function testBury()
    {
        $command = new BuryCommand(new JobId(5), 2);
        $this->assertCommandLine($command, 'bury 5 2');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('BURIED'), null),
            ResponseInterface::RESPONSE_BURIED
        );
    }

    public function testDelete()
    {
        $command = new DeleteCommand(new JobId(5));
        $this->assertCommandLine($command, 'delete 5');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('DELETED'), null),
            ResponseInterface::RESPONSE_DELETED
        );
    }

    public function testIgnore()
    {
        $command = new IgnoreCommand('tube1');
        $this->assertCommandLine($command, 'ignore tube1');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('WATCHING 2'), null),
            ResponseInterface::RESPONSE_WATCHING,
            ['count' => 2]
        );
    }

    public function testIgnoreBadResponse()
    {
        $command = new IgnoreCommand('tube1');
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse(ResponseLine::fromString(__FUNCTION__), null);
    }

    public function testPauseTubeBadResponse()
    {
        $command = new PauseTubeCommand('tube1', 1);
        $this->expectError();
        $command->getResponseParser()->parseResponse(ResponseLine::fromString(__FUNCTION__), null);
    }

    public function testBuryBadResponse()
    {
        $command = new BuryCommand(new JobId(15), 1);
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse(ResponseLine::fromString(__FUNCTION__), null);
    }

    public function testPeekBadResponse()
    {
        $command = new PeekCommand(PeekCommand::TYPE_BURIED);
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse(ResponseLine::fromString(__FUNCTION__), null);
    }

    public function testPeekJobBadResponse()
    {
        $command = new PeekJobCommand(new JobId(15));
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse(ResponseLine::fromString(__FUNCTION__), null);
    }

    public function testKickJobBadResponse()
    {
        $command = new KickJobCommand(new JobId(15));
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse(ResponseLine::fromString(__FUNCTION__), null);
    }

    public function testKickJobNotFound()
    {
        $command = new KickJobCommand(new JobId(15));
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse(ResponseLine::fromString(ResponseInterface::RESPONSE_NOT_FOUND), null);
    }

    public function testKick()
    {
        $command = new KickCommand(5);
        $this->assertCommandLine($command, 'kick 5');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('KICKED 2'), null),
            ResponseInterface::RESPONSE_KICKED,
            ['kicked' => 2]
        );
    }

    public function testKickJob()
    {
        $command = new KickJobCommand(new JobId(5));
        $this->assertCommandLine($command, 'kick-job 5');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('KICKED'), null),
            ResponseInterface::RESPONSE_KICKED
        );
    }

    public function testListTubesWatched()
    {
        $command = new ListTubesWatchedCommand();
        $this->assertCommandLine($command, 'list-tubes-watched');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('OK 16'), "---\n- one\n- two\n"),
            ResponseInterface::RESPONSE_OK,
            ['one', 'two']
        );
    }

    public function testListTubeUsed()
    {
        $command = new ListTubeUsedCommand();
        $this->assertCommandLine($command, 'list-tube-used');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('USING default'), null),
            ResponseInterface::RESPONSE_USING,
            ['tube' => 'default']
        );
    }

    public function testPut()
    {
        $command = new PutCommand('data', 5, 6, 7);
        $this->assertCommandLine($command, 'put 5 6 7 4', true);
        $this->assertEquals('data', $command->getData());

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('INSERTED 4'), null),
            ResponseInterface::RESPONSE_INSERTED,
            ['id' => '4']
        );
    }

    public function testPutBuried()
    {
        $command = new PutCommand('data', 5, 6, 7);
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse(ResponseLine::fromString('BURIED 4'), null);
    }

    public function testRelease()
    {
        $job = new JobId(3);
        $command = new ReleaseCommand($job, 1, 0);
        $this->assertCommandLine($command, 'release 3 1 0');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('RELEASED'), null),
            ResponseInterface::RESPONSE_RELEASED
        );
    }

    public function testReserve()
    {
        $command = new ReserveCommand();
        $this->assertCommandLine($command, 'reserve');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('RESERVED 5 9'), 'test data'),
            ResponseInterface::RESPONSE_RESERVED,
            ['id' => 5, 'jobdata' => 'test data']
        );
    }

    public function testReserveJob()
    {
        $job = new JobId(4);
        $command = new ReserveJobCommand($job);
        $this->assertCommandLine($command, 'reserve-job 4');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('RESERVED 5 9'), 'test data'),
            ResponseInterface::RESPONSE_RESERVED,
            ['id' => 5, 'jobdata' => 'test data']
        );
    }

    public function testReserveJobNotFound()
    {
        $job = new JobId(5);
        $command = new ReserveJobCommand($job);
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse(ResponseLine::fromString(ResponseInterface::RESPONSE_NOT_FOUND), null);
    }

    public function testReserveDeadline()
    {
        $this->expectException(DeadlineSoonException::class);
        $command = new ReserveCommand();

        $command->getResponseParser()->parseResponse(ResponseLine::fromString('DEADLINE_SOON'), null);
    }

    public function testUse()
    {
        $command = new UseCommand('tube5');
        $this->assertCommandLine($command, 'use tube5');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('USING tube5'), null),
            ResponseInterface::RESPONSE_USING,
            ['tube' => 'tube5']
        );
    }

    public function testWatch()
    {
        $command = new WatchCommand('tube6');
        $this->assertCommandLine($command, 'watch tube6');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('WATCHING 3'), null),
            ResponseInterface::RESPONSE_WATCHING,
            ['count' => '3']
        );
    }

    public function testReserveWithTimeout()
    {
        $command = new ReserveWithTimeoutCommand(10);
        $this->assertCommandLine($command, 'reserve-with-timeout 10');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('TIMED_OUT'), null),
            ResponseInterface::RESPONSE_TIMED_OUT
        );
    }

    public function testTouch()
    {
        $command = new TouchCommand(new JobId(5));
        $this->assertCommandLine($command, 'touch 5');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('TOUCHED'), null),
            ResponseInterface::RESPONSE_TOUCHED
        );
    }

    public function testListTubes()
    {
        $command = new ListTubesCommand();
        $this->assertCommandLine($command, 'list-tubes');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('OK 16'), "---\n- one\n- two\n"),
            ResponseInterface::RESPONSE_OK,
            ['one', 'two']
        );
    }

    public function testPeek()
    {
        $command = new PeekJobCommand(new JobId(5));
        $this->assertCommandLine($command, 'peek 5');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('FOUND 5 9'), 'test data'),
            ResponseInterface::RESPONSE_FOUND,
            ['id' => 5, 'jobdata' => 'test data']
        );
    }

    public function testPeekReady()
    {
        $command = new PeekCommand('ready');
        $this->assertCommandLine($command, 'peek-ready');
    }

    public function testPeekDelayed()
    {
        $command = new PeekCommand('delayed');
        $this->assertCommandLine($command, 'peek-delayed');
    }

    public function testPeekBuried()
    {
        $command = new PeekCommand('buried');
        $this->assertCommandLine($command, 'peek-buried');
    }

    public function testStatsJob()
    {
        $command = new StatsJobCommand(new JobId(5));
        $this->assertCommandLine($command, 'stats-job 5');

        $data = "---\nid: 8\ntube: test\nstate: delayed\n";

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('OK ' . strlen($data)), $data),
            ResponseInterface::RESPONSE_OK,
            ['id' => '8', 'tube' => 'test', 'state' => 'delayed']
        );
    }

    public function testStatsTube()
    {
        $command = new StatsTubeCommand('test');
        $this->assertCommandLine($command, 'stats-tube test');

        $data = "---\nname: test\ncurrent-jobs-ready: 5\n";

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('OK ' . strlen($data)), $data),
            ResponseInterface::RESPONSE_OK,
            ['name' => 'test', 'current-jobs-ready' => '5']
        );
    }

    public function testStats()
    {
        $command = new StatsCommand();
        $this->assertCommandLine($command, 'stats');

        $data = "---\npid: 123\nversion: 1.3\n";

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('OK ' . strlen($data)), $data),
            ResponseInterface::RESPONSE_OK,
            ['pid' => '123', 'version' => '1.3']
        );
    }

    public function testPauseTube()
    {
        $command = new PauseTubeCommand('testtube7', 10);
        $this->assertCommandLine($command, 'pause-tube testtube7 10');
        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('PAUSED'), null),
            ResponseInterface::RESPONSE_PAUSED
        );
    }

    public function testIssue12YamlParsingMissingValue()
    {
        // missing version number
        $data = "---\npid: 123\nversion: \nkey: value\n";

        $command = new StatsCommand();

        $this->assertResponse(
            $command->getResponseParser()->parseResponse(ResponseLine::fromString('OK ' . strlen($data)), $data),
            ResponseInterface::RESPONSE_OK,
            ['pid' => '123', 'version' => '', 'key' => 'value']
        );
    }

    // ----------------------------------------

    private function assertCommandLine(CommandInterface $command, string $expected, bool $expectData = false)
    {
        $this->assertSame($expected, $command->getCommandLine());
        $this->assertSame($expectData, $command->hasData());
    }

    /**
     * @param ResponseInterface $response
     * @param string   $expectName
     * @param array    $data
     */
    private function assertResponse(ResponseInterface $response, string $expectName, array $data = [])
    {
        $this->assertSame($expectName, $response->getResponseName());
        $this->assertEquals($data, iterator_to_array($response));
    }
}
