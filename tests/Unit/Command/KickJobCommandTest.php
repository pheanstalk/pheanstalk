<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\KickJobCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(KickJobCommand::class)]
final class KickJobCommandTest extends JobCommandTestBase
{
    public function testInterpretKicked(): void
    {
        $command = $this->getSubject();
        $command->interpret(new RawResponse(ResponseType::Kicked, null));
        $this->expectNotToPerformAssertions();
    }

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Kicked];
    }

    protected function getSubject(?JobIdInterface $jobId = null): KickJobCommand
    {
        return new KickJobCommand($jobId ?? new JobId(5));
    }
}
