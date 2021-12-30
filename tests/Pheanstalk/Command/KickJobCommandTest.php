<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\KickJobCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\JobId;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\Success;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\KickJobCommand
 */
final class KickJobCommandTest extends JobCommandTest
{
    public function testInterpretKicked(): void
    {
        $command = $this->getSubject();
        $command->interpret(new RawResponse(ResponseType::Kicked, null));
        $this->expectNotToPerformAssertions();
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Kicked];
    }

    protected function getSubject(JobIdInterface $jobId = null): KickJobCommand
    {
        return new KickJobCommand($jobId ?? new JobId(5));
    }
}
