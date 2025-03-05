<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Unit\Command;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Exception\UnsupportedResponseException;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ResponseType;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

abstract class CommandTestBase extends TestCase
{
    /**
     * @return list<ResponseType>
     */
    abstract protected static function getSupportedResponses(): array;

    abstract protected function getSubject(): CommandInterface;

    /**
     * @phpstan-return iterable<array{0: ResponseType}>
     */
    final public static function supportedResponseProvider(): iterable
    {
        foreach (static::getSupportedResponses() as $responseType) {
            yield [$responseType];
        }
    }

    /**
     * This test confirms that for each response type that the command supports at least one test method exists.
     * Note: While this test failing is a clear indicator something is wrong, passing this test does not mean that
     * your test class is properly covering all or any functionality of the command class
     */
    #[DataProvider('supportedResponseProvider')]
    final public function testSupportedResponseIsTested(ResponseType $type): void
    {
        $method = "testInterpret" . ucfirst($type->name);
        Assert::assertTrue(method_exists($this, $method), "It seems a test for response {$type->value} is missing or misnamed. Could not find method '$method' on " . static::class);
    }

    /**
     * @phpstan-return iterable<array{0: ResponseType}>
     */
    final public static function unsupportedResponseProvider(): iterable
    {
        $supported = static::getSupportedResponses();
        foreach (ResponseType::cases() as $responseType) {
            if (!in_array($responseType, $supported, true)) {
                yield [$responseType];
            }
        }
    }

    #[DataProvider('unsupportedResponseProvider')]
    final public function testUnsupportedResponses(ResponseType $type): void
    {
        $this->expectException(UnsupportedResponseException::class);
        $this->getSubject()->interpret(new RawResponse($type));
    }
}
