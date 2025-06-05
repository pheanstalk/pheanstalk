<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Exception;

use Pheanstalk\Exception\TubeNotFoundException;
use Pheanstalk\Values\TubeName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(TubeNotFoundException::class)]
final class TubeNotFoundExceptionTest extends TestCase
{
    #[TestWith(['', 'Tube "[unknown]" not found.', '[unknown]'])]
    #[TestWith(['Custom message', 'Custom message', '[unknown]'])]
    #[TestWith([new TubeName('foo'), 'Tube "foo" not found.', 'foo'])]
    public function testMessageAndTubeAreAsExpected(string|TubeName $message, string $expectedMessage, string $expectedTube): void
    {
        $exception = new TubeNotFoundException($message);

        self::assertSame($expectedMessage, $exception->getMessage());
        self::assertSame($expectedTube, $exception->tube);
    }
}
