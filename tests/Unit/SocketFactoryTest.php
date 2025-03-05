<?php

declare(strict_types=1);


namespace Pheanstalk\Tests\Unit;

use Pheanstalk\SocketFactory;
use Pheanstalk\Values\SocketImplementation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SocketFactory::class)]
final class SocketFactoryTest extends TestCase
{
    public function testDetection(): void
    {
        $factory = new SocketFactory('invalid host');
        self::assertSame(SocketImplementation::SOCKET, $factory->implementation);
    }
}
