<?php

declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Contract\SocketFactoryInterface;
use Pheanstalk\Values\Timeout;

/**
 * @internal
 */
trait StaticFactoryTrait
{
    public function __construct(private readonly Connection $connection)
    {
    }
    /**
     * Static constructor that uses auto-detection to choose an underlying socket implementation
     */
    public static function create(
        string $host,
        int $port = 11300,
        Timeout $connectTimeout = null,
        Timeout $receiveTimeout = null
    ): self {
        return self::createWithFactory(new SocketFactory($host, $port, null, $connectTimeout, $receiveTimeout));
    }

    /**
     * Static constructor that uses a given socket factory for underlying connections
     */
    public static function createWithFactory(SocketFactoryInterface $factory): self
    {
        return new self(new Connection($factory));
    }
}
