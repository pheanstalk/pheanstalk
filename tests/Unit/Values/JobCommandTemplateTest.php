<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Values;

use InvalidArgumentException;
use Pheanstalk\Values\JobCommandTemplate;
use Pheanstalk\Values\JobId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JobCommandTemplate::class)]
final class JobCommandTemplateTest extends TestCase
{
    public function testMissingPlaceholder(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new JobCommandTemplate('invalid');
    }

    public function testRender(): void
    {
        self::assertSame('test 123', (new JobCommandTemplate('test {id}'))->render(new JobId(123)));
    }
}
