<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Values;

use Pheanstalk\Values\Job;
use Pheanstalk\Values\JobId;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Pheanstalk\Values\Job
 */
class JobTest extends TestCase
{
    public function testConstructor(): void
    {
        $jobId = new JobId('123');
        $data = random_bytes(132);
        $job = new Job($jobId, $data);

        self::assertSame($jobId->getId(), $job->getId());
        self::assertSame($data, $job->getData());
    }
}
