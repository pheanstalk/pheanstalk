<?php

declare(strict_types=1);

namespace Pheanstalk\Contract;

interface SocketInterface
{
    /**
     * Writes data to the socket.
     */
    public function write(string $data): void;

    /**
     * Reads up to $length bytes from the socket.
     * @param int<0, max> $length
     */
    public function read(int $length): string;

    /**
     * Reads up to the next new-line.
     * Trailing whitespace is trimmed.
     */
    public function getLine(): string;

    /**
     * Disconnect the socket; subsequent usage of the socket will fail.
     */
    public function disconnect(): void;
}
