<?php

declare(strict_types=1);


namespace Pheanstalk\Tests\Integration;

use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Contract\SocketInterface;
use Pheanstalk\Exception\ConnectionException;
use Pheanstalk\Socket\FsockopenSocket;
use Pheanstalk\Socket\SocketSocket;
use Pheanstalk\Socket\StreamSocket;
use Pheanstalk\SocketFactory;
use Pheanstalk\Values\SocketImplementation;
use Pheanstalk\Values\Timeout;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SocketFactory::class)]
#[CoversClass(SocketSocket::class)]
#[CoversClass(FsockopenSocket::class)]
#[CoversClass(StreamSocket::class)]
final class SocketFactoryTest extends TestCase
{
    /**
     * @phpstan-return iterable<array{0: SocketFactory, 1: class-string}>
     */
    public static function factoryProvider(): iterable
    {
        if (SERVER_HOST !== '') {
            yield [new SocketFactory(SERVER_HOST, implementation: SocketImplementation::SOCKET), SocketSocket::class];
            yield [new SocketFactory(SERVER_HOST, implementation: SocketImplementation::STREAM), StreamSocket::class];
            yield [
                new SocketFactory(SERVER_HOST, implementation: SocketImplementation::FSOCKOPEN),
                FsockopenSocket::class
            ];
        }
        if (UNIX_SERVER_HOST !== '') {
            yield [
                new SocketFactory(UNIX_SERVER_HOST, implementation: SocketImplementation::SOCKET),
                SocketSocket::class
            ];
            yield [
                new SocketFactory(UNIX_SERVER_HOST, implementation: SocketImplementation::STREAM),
                StreamSocket::class
            ];
            yield [
                new SocketFactory(UNIX_SERVER_HOST, implementation: SocketImplementation::FSOCKOPEN),
                FsockopenSocket::class
            ];
        }
    }

    /**
     * @param class-string<SocketInterface> $expectedImplementationClass
     */
    #[DataProvider('factoryProvider')]
    public function testImplementations(SocketFactoryInterface $factory, string $expectedImplementationClass): void
    {
        Assert::assertInstanceOf($expectedImplementationClass, $factory->create());
    }

    /**
     * @return iterable<array{0: string, 1: SocketImplementation|null}>
     */
    public static function invalidHostProvider(): iterable
    {
        $hosts = ['not-valid', '192.0.2.123', 'unix:///invalid-file'];
        $implementations = [...SocketImplementation::cases(), null];
        foreach ($hosts as $host) {
            foreach ($implementations as $implementation) {
                yield [$host, $implementation];
            }
        }
    }

    #[DataProvider("invalidHostProvider")]
    public function testExceptionOnNonExistentHost(string $host, SocketImplementation|null $implementation): void
    {
        $factory = new SocketFactory(host: $host, implementation: $implementation, connectTimeout: new Timeout(0, 500000));
        $this->expectException(ConnectionException::class);
        $factory->create();
    }
}
