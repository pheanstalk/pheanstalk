<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Command\DeleteCommand;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteCommand::class)]
final class DeleteCommandTest extends JobCommandTestBase
{
    public function testInterpretDeleted(): void
    {
        $command = $this->getSubject();
        $command->interpret(new RawResponse(ResponseType::Deleted, null));
        $this->expectNotToPerformAssertions();
    }

    protected static function getSupportedResponses(): array
    {
        return [ResponseType::NotFound, ResponseType::Deleted];
    }

    protected function getSubject(?JobIdInterface $jobId = null): DeleteCommand
    {
        return new DeleteCommand($jobId ?? new JobId(5));
    }
}
