<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Values;

use InvalidArgumentException;
use Pheanstalk\Values\JobId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(JobId::class)]
final class JobIdTest extends TestCase
{
    /**
     * @return list<array{0: string|int}>
     */
    public static function validSamples(): array
    {
        return [
            ['123'],
            [123],
        ];
    }

    /**
     * @return list<array{0: string|int}>
     */
    public static function invalidSamples(): array
    {
        return [
            ['ab'],
            ['123ab'],
            ['ab123'],
            [-15],

        ];
    }

    #[DataProvider('validSamples')]
    public function testConstructor(int|string $value): void
    {
        $jobId = new JobId($value);

        self::assertSame((string) $value, $jobId->getId());

        $nested = new JobId($jobId);
        self::assertSame($jobId->getId(), $nested->getId());
    }

    #[DataProvider('invalidSamples')]
    public function testConstructorException(int|string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new JobId($value);
    }
}
