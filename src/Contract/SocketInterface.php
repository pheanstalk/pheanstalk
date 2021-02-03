<?php
declare(strict_types=1);

namespace Pheanstalk\Contract;

/**
 * A mockable wrapper around PHP "socket" or "file pointer" access.
 *
 * Only the subset of socket actions required by Pheanstalk are provided.
 */
interface SocketInterface
{
    /**
     * Writes data to the socket.
     */
    public function write(string $data): void;

    /**
     * Reads up to $length bytes from the socket.
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
