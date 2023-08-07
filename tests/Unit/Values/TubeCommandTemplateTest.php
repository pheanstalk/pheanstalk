<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Values;

use Pheanstalk\Values\TubeCommandTemplate;
use Pheanstalk\Values\TubeName;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Pheanstalk\Values\TubeCommandTemplate
 */
final class TubeCommandTemplateTest extends TestCase
{
    public function testMissingPlaceholder(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TubeCommandTemplate('invalid');
    }

    public function testRender(): void
    {
        self::assertSame('this is cool', (new TubeCommandTemplate('this is {tube}'))->render(new TubeName('cool')));
    }
}
