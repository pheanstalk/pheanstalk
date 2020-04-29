<?php

namespace Pheanstalk\Contract;

/**
 * A mockable wrapper around PHP "socket" or "file pointer" access.
 *
 * Only the subset of socket actions required by Pheanstalk are provided.
 *
 * @author  Paul Annesley
 */
interface SocketInterface
{
    /**
     * Writes data to the socket.
     *
     * @param string $data
     *
     * @return void
     */
    public function write(string $data): void;

    /**
     * Reads up to $length bytes from the socket.
     *
     * @return string
     */
    public function read(int $length): string;

    /**
     * Reads up to the next new-line.
     * Trailing whitespace is trimmed.
     *
     * @param int
     */
    public function getLine(): string;

    /**
     * Disconnect the socket; subsequent usage of the socket will fail.
     */
    public function disconnect(): void;
}
