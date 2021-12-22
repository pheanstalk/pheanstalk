<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Command\PeekCommand;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Exception\DeadlineSoonException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Command implementations.
 *
 * @author  Paul Annesley
 */
class CommandTest extends TestCase
{
    public function testBury(): void
    {
        $command = new Command\BuryCommand(new JobId(5), 2);
        $this->assertCommandLine($command, 'bury 5 2');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('BURIED', null),
            ResponseInterface::RESPONSE_BURIED
        );
    }

    public function testDelete(): void
    {
        $command = new Command\DeleteCommand(new JobId(5));
        $this->assertCommandLine($command, 'delete 5');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('DELETED', null),
            ResponseInterface::RESPONSE_DELETED
        );
    }

    public function testIgnore(): void
    {
        $command = new Command\IgnoreCommand('tube1');
        $this->assertCommandLine($command, 'ignore tube1');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('WATCHING 2', null),
            ResponseInterface::RESPONSE_WATCHING,
            ['count' => 2]
        );
    }

    public function testIgnoreBadResponse(): void
    {
        $command = new Command\IgnoreCommand('tube1');
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse(__FUNCTION__, null);
    }

    public function testPauseTubeBadResponse(): void
    {
        $command = new Command\PauseTubeCommand('tube1', 1);
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse(__FUNCTION__, null);
    }

    public function testBuryBadResponse(): void
    {
        $command = new Command\BuryCommand(new JobId(15), 1);
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse(__FUNCTION__, null);
    }

    public function testPeekBadResponse(): void
    {
        $command = new Command\PeekCommand(PeekCommand::TYPE_BURIED);
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse(__FUNCTION__, null);
    }

    public function testPeekJobBadResponse(): void
    {
        $command = new Command\PeekJobCommand(new JobId(15));
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse(__FUNCTION__, null);
    }

    public function testKickJobBadResponse(): void
    {
        $command = new Command\KickJobCommand(new JobId(15));
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse(__FUNCTION__, null);
    }

    public function testKickJobNotFound(): void
    {
        $command = new Command\KickJobCommand(new JobId(15));
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse(ResponseInterface::RESPONSE_NOT_FOUND, null);
    }

    public function testKick()
    {
        $command = new Command\KickCommand(5);
        $this->assertCommandLine($command, 'kick 5');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('KICKED 2', null),
            ResponseInterface::RESPONSE_KICKED,
            ['kicked' => 2]
        );
    }

    public function testKickJob(): void
    {
        $command = new Command\KickJobCommand(new JobId(5));
        $this->assertCommandLine($command, 'kick-job 5');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('KICKED', null),
            ResponseInterface::RESPONSE_KICKED
        );
    }

    public function testListTubesWatched(): void
    {
        $command = new Command\ListTubesWatchedCommand();
        $this->assertCommandLine($command, 'list-tubes-watched');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('OK 16', "---\n- one\n- two\n"),
            ResponseInterface::RESPONSE_OK,
            ['one', 'two']
        );
    }

    public function testListTubeUsed(): void
    {
        $command = new Command\ListTubeUsedCommand();
        $this->assertCommandLine($command, 'list-tube-used');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('USING default', null),
            ResponseInterface::RESPONSE_USING,
            ['tube' => 'default']
        );
    }

    public function testPut(): void
    {
        $command = new Command\PutCommand('data', 5, 6, 7);
        $this->assertCommandLine($command, 'put 5 6 7 4', true);
        Assert::assertEquals($command->getData(), 'data');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('INSERTED 4', null),
            ResponseInterface::RESPONSE_INSERTED,
            ['id' => '4']
        );
    }

    public function testPutBuried(): void
    {
        $command = new Command\PutCommand('data', 5, 6, 7);
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse('BURIED 4', null);
    }

    public function testRelease(): void
    {
        $job = new JobId(3);
        $command = new Command\ReleaseCommand($job, 1, 0);
        $this->assertCommandLine($command, 'release 3 1 0');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('RELEASED', null),
            ResponseInterface::RESPONSE_RELEASED
        );
    }

    public function testReserve(): void
    {
        $command = new Command\ReserveCommand();
        $this->assertCommandLine($command, 'reserve');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('RESERVED 5 9', 'test data'),
            ResponseInterface::RESPONSE_RESERVED,
            ['id' => 5, 'jobdata' => 'test data']
        );
    }

    public function testReserveDeadline(): void
    {
        $this->expectException(DeadlineSoonException::class);
        $command = new Command\ReserveCommand();

        $command->getResponseParser()->parseResponse('DEADLINE_SOON', null);
    }

    public function testUse(): void
    {
        $command = new Command\UseCommand('tube5');
        $this->assertCommandLine($command, 'use tube5');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('USING tube5', null),
            ResponseInterface::RESPONSE_USING,
            ['tube' => 'tube5']
        );
    }

    public function testWatch(): void
    {
        $command = new Command\WatchCommand('tube6');
        $this->assertCommandLine($command, 'watch tube6');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('WATCHING 3', null),
            ResponseInterface::RESPONSE_WATCHING,
            ['count' => '3']
        );
    }

    public function testReserveWithTimeout(): void
    {
        $command = new Command\ReserveWithTimeoutCommand(10);
        $this->assertCommandLine($command, 'reserve-with-timeout 10');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('TIMED_OUT', null),
            ResponseInterface::RESPONSE_TIMED_OUT
        );
    }

    public function testTouch(): void
    {
        $command = new Command\TouchCommand(new JobId(5));
        $this->assertCommandLine($command, 'touch 5');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('TOUCHED', null),
            ResponseInterface::RESPONSE_TOUCHED
        );
    }

    public function testListTubes(): void
    {
        $command = new Command\ListTubesCommand();
        $this->assertCommandLine($command, 'list-tubes');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('OK 16', "---\n- one\n- two\n"),
            ResponseInterface::RESPONSE_OK,
            ['one', 'two']
        );
    }

    public function testPeek(): void
    {
        $command = new Command\PeekJobCommand(new JobId(5));
        $this->assertCommandLine($command, 'peek 5');

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('FOUND 5 9', 'test data'),
            ResponseInterface::RESPONSE_FOUND,
            ['id' => 5, 'jobdata' => 'test data']
        );
    }

    public function testPeekReady(): void
    {
        $command = new Command\PeekCommand('ready');
        $this->assertCommandLine($command, 'peek-ready');
    }

    public function testPeekDelayed(): void
    {
        $command = new Command\PeekCommand('delayed');
        $this->assertCommandLine($command, 'peek-delayed');
    }

    public function testPeekBuried(): void
    {
        $command = new Command\PeekCommand('buried');
        $this->assertCommandLine($command, 'peek-buried');
    }

    public function testStatsJob(): void
    {
        $command = new Command\StatsJobCommand(new JobId(5));
        $this->assertCommandLine($command, 'stats-job 5');

        $data = "---\nid: 8\ntube: test\nstate: delayed\n";

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('OK ' . strlen($data), $data),
            ResponseInterface::RESPONSE_OK,
            ['id' => '8', 'tube' => 'test', 'state' => 'delayed']
        );
    }

    public function testStatsTube(): void
    {
        $command = new Command\StatsTubeCommand('test');
        $this->assertCommandLine($command, 'stats-tube test');

        $data = "---\nname: test\ncurrent-jobs-ready: 5\n";

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('OK ' . strlen($data), $data),
            ResponseInterface::RESPONSE_OK,
            ['name' => 'test', 'current-jobs-ready' => '5']
        );
    }

    public function testStats(): void
    {
        $command = new Command\StatsCommand();
        $this->assertCommandLine($command, 'stats');

        $data = "---\npid: 123\nversion: 1.3\n";

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('OK ' . strlen($data), $data),
            ResponseInterface::RESPONSE_OK,
            ['pid' => '123', 'version' => '1.3']
        );
    }

    public function testPauseTube(): void
    {
        $command = new Command\PauseTubeCommand('testtube7', 10);
        $this->assertCommandLine($command, 'pause-tube testtube7 10');
        $this->assertResponse(
            $command->getResponseParser()->parseResponse('PAUSED', null),
            ResponseInterface::RESPONSE_PAUSED
        );
    }

    public function testIssue12YamlParsingMissingValue(): void
    {
        // missing version number
        $data = "---\npid: 123\nversion: \nkey: value\n";

        $command = new Command\StatsCommand();

        $this->assertResponse(
            $command->getResponseParser()->parseResponse('OK ' . strlen($data), $data),
            ResponseInterface::RESPONSE_OK,
            ['pid' => '123', 'version' => '', 'key' => 'value']
        );
    }

    // ----------------------------------------

    private function assertCommandLine(CommandInterface $command, string $expected, bool $expectData = false): void
    {
        Assert::assertEquals($expected, $command->getCommandLine());
        Assert::assertEquals($expectData, $command->hasData());
    }

    /**
     * @param ResponseInterface $response
     * @param string   $expectName
     * @param array    $data
     */
    private function assertResponse(ResponseInterface $response, string $expectName, array $data = []): void
    {
        Assert::assertEquals($expectName, $response->getResponseName());
        Assert::assertEquals($data, iterator_to_array($response));
    }
}
