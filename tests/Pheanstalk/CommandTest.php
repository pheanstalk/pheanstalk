<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Command\PeekCommand;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Exception\JobNotFoundException;
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

        $this->assertResponse($command, ResponseType::BURIED);
    }

    public function testDelete(): void
    {
        $command = new Command\DeleteCommand(new JobId(5));
        $this->assertCommandLine($command, 'delete 5');

        $this->assertResponse($command, ResponseType::DELETED);
    }

    private function assertResponse(
        CommandInterface $command,
        ResponseType $responseType,
        array $arguments = [],
        string $data = null,
        ResponseParserInterface $parser = null,

    ): void
    {
        $response = ($parser ?? $command->getResponseParser())->parseResponse($command, $responseType, $arguments, $data);
        Assert::assertEquals($responseType, $response->getResponseType());

        if (isset($data)) {
            self::markTestIncomplete('Implement data validation');
//            Assert::assertInstanceOf();
        }
    }

    public function testIgnore(): void
    {
        $command = new Command\IgnoreCommand(new TubeName('tube1'));
        $this->assertCommandLine($command, 'ignore tube1');

        $this->assertResponse($command, ResponseType::WATCHING, ["2"]);
    }


    public function testKickJobNotFound(): void
    {
        $command = new Command\KickJobCommand(new JobId(15));
        $this->expectException(JobNotFoundException::class);
        $result = $command->getResponseParser()->parseResponse($command, ResponseType::NOT_FOUND);
        var_dump($command->getResponseParser());
        var_dump($result); die();
    }

    public function testKick()
    {
        $command = new Command\KickCommand(5);
        $this->assertCommandLine($command, 'kick 5');

        $this->assertResponse($command, ResponseType::KICKED, ["2"]);
    }

    public function testKickJob(): void
    {
        $command = new Command\KickJobCommand(new JobId(5));
        $this->assertCommandLine($command, 'kick-job 5');

        $this->assertResponse($command, ResponseType::KICKED);
    }

    public function testListTubesWatched(): void
    {
        $command = new Command\ListTubesWatchedCommand();
        $this->assertCommandLine($command, 'list-tubes-watched');

        $this->assertResponse($command, ResponseType::OK, ["16"], "---\n- one\n- two\n");
    }

    public function testListTubeUsed(): void
    {
        $command = new Command\ListTubeUsedCommand();
        $this->assertCommandLine($command, 'list-tube-used');

        $this->assertResponse($command, ResponseType::USING, ["default"]);
    }

    public function testPut(): void
    {
        $command = new Command\PutCommand('data', 5, 6, 7);
        $this->assertCommandLine($command, 'put 5 6 7 4', true);
        Assert::assertEquals($command->getData(), 'data');
        $this->assertResponse($command, ResponseType::INSERTED, ["4"]);
    }

    public function testPutBuried(): void
    {
        $command = new Command\PutCommand('data', 5, 6, 7);
        $this->expectException(Exception::class);
        $command->getResponseParser()->parseResponse($command, ResponseType::BURIED, ["4"]);
    }

    public function testRelease(): void
    {
        $job = new JobId(3);
        $command = new Command\ReleaseCommand($job, 1, 0);
        $this->assertCommandLine($command, 'release 3 1 0');

        $this->assertResponse($command, ResponseType::RELEASED);
    }

    public function testReserve(): void
    {
        $command = new Command\ReserveCommand();
        $this->assertCommandLine($command, 'reserve');

        $this->assertResponse($command, ResponseType::RESERVED, ["5", "9"], 'test data');
    }

    public function testReserveDeadline(): void
    {
        $this->expectException(DeadlineSoonException::class);
        $command = new Command\ReserveCommand();

        $command->getResponseParser()->parseResponse($command, ResponseType::DEADLINE_SOON);
    }

    public function testUse(): void
    {
        $command = new Command\UseCommand(new TubeName('tube5'));
        $this->assertCommandLine($command, 'use tube5');

        $this->assertResponse($command, ResponseType::USING, ["tube5"]);
    }

    public function testWatch(): void
    {
        $command = new Command\WatchCommand(new TubeName('tube6'));
        $this->assertCommandLine($command, 'watch tube6');

        $this->assertResponse($command, ResponseType::WATCHING, ["3"]);
    }

    public function testReserveWithTimeout(): void
    {
        $command = new Command\ReserveWithTimeoutCommand(10);
        $this->assertCommandLine($command, 'reserve-with-timeout 10');

        $this->assertResponse($command, ResponseType::TIMED_OUT);
    }

    public function testTouch(): void
    {
        $command = new Command\TouchCommand(new JobId(5));
        $this->assertCommandLine($command, 'touch 5');

        $this->assertResponse($command, ResponseType::TOUCHED);
    }

    public function testListTubes(): void
    {
        $command = new Command\ListTubesCommand();
        $this->assertCommandLine($command, 'list-tubes');

        $this->assertResponse($command, ResponseType::OK, ["16"], "---\n- one\n- two\n");
    }

    public function testPeek(): void
    {
        $command = new Command\PeekJobCommand(new JobId(5));
        $this->assertCommandLine($command, 'peek 5');

        $this->assertResponse($command, ResponseType::FOUND, ["5", "9"], 'test data');
    }

    public function testPeekReady(): void
    {
        $command = new Command\PeekCommand(CommandType::PEEK_READY);
        $this->assertCommandLine($command, 'peek-ready');
    }

    public function testPeekDelayed(): void
    {
        $command = new Command\PeekCommand(CommandType::PEEK_DELAYED);
        $this->assertCommandLine($command, 'peek-delayed');
    }

    public function testPeekBuried(): void
    {
        $command = new Command\PeekCommand(CommandType::PEEK_BURIED);
        $this->assertCommandLine($command, 'peek-buried');
    }

    public function testStatsJob(): void
    {
        $command = new Command\StatsJobCommand(new JobId(5));
        $this->assertCommandLine($command, 'stats-job 5');

        $data = "---\nid: 8\ntube: test\nstate: delayed\n";

        $this->assertResponse($command, ResponseType::OK, [strlen($data)], $data);
    }

    public function testStatsTube(): void
    {
        $command = new Command\StatsTubeCommand(new TubeName('test'));
        $this->assertCommandLine($command, 'stats-tube test');

        $data = "---\nname: test\ncurrent-jobs-ready: 5\n";

        $this->assertResponse($command, ResponseType::OK, [strlen($data)], $data);
    }

    public function testStats(): void
    {
        $command = new Command\StatsCommand();
        $this->assertCommandLine($command, 'stats');

        $data = "---\npid: 123\nversion: 1.3\n";

        $this->assertResponse($command, ResponseType::OK, [strlen($data)], $data);
    }

    public function testPauseTube(): void
    {
        $command = new Command\PauseTubeCommand(new TubeName('testtube7'), 10);
        $this->assertCommandLine($command, 'pause-tube testtube7 10');
        $this->assertResponse($command, ResponseType::PAUSED);
    }

    public function testIssue12YamlParsingMissingValue(): void
    {
        // missing version number
        $data = "---\npid: 123\nversion: \nkey: value\n";

        $command = new Command\StatsCommand();

        $this->assertResponse($command, ResponseType::OK, [strlen($data)], $data);
    }

    // ----------------------------------------

    private function assertCommandLine(CommandInterface $command, string $expected, bool $expectData = false): void
    {
        Assert::assertEquals($expected, $command->getCommandLine());
        Assert::assertEquals($expectData, $command->hasData());
    }


}
