<?php

declare(strict_types=1);


namespace Pheanstalk\Tests\Integration;

use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Socket\FsockopenSocket;
use Pheanstalk\Socket\SocketSocket;
use Pheanstalk\Socket\StreamSocket;
use Pheanstalk\SocketFactory;
use Pheanstalk\Values\SocketImplementation;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Pheanstalk\SocketFactory
 */
final class SocketFactoryTest extends TestCase
{
    /**
     * @phpstan-return iterable<array{0: SocketFactory, 1: class-string}>
     */
    public function factoryProvider(): iterable
    {
        yield [new SocketFactory(SERVER_HOST, 11300, SocketImplementation::SOCKET), SocketSocket::class];
        yield [new SocketFactory(SERVER_HOST, 11300, SocketImplementation::STREAM), StreamSocket::class];
        yield [new SocketFactory(SERVER_HOST, 11300, SocketImplementation::FSOCKOPEN), FsockopenSocket::class];
    }

    /**
     * @dataProvider factoryProvider
     * @param class-string<SocketInterface> $expectedImplementationClass
     */
    public function testImplementations(SocketFactoryInterface $factory, string $expectedImplementationClass): void
    {
        Assert::assertInstanceOf($expectedImplementationClass, $factory->create());
    }
}
