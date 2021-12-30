<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Command;

use Pheanstalk\Command\DeleteCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\JobId;
use Pheanstalk\RawResponse;
use Pheanstalk\ResponseType;
use Pheanstalk\Success;
use PHPUnit\Framework\Assert;

/**
 * @covers \Pheanstalk\Command\DeleteCommand
 */
final class DeleteCommandTest extends JobCommandTest
{
    public function testInterpretDeleted(): void
    {
        $command = $this->getSubject();
        $command->interpret(new RawResponse(ResponseType::Deleted, null));
        $this->expectNotToPerformAssertions();
    }

    protected function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Deleted];
    }

    protected function getSubject(JobIdInterface $jobId = null): DeleteCommand
    {
        return new DeleteCommand($jobId ?? new JobId(5));
    }
}
