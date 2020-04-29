<?php

namespace Pheanstalk\Socket;

/**
 * A limited history of recent socket write length/success.
 *
 * Facilitates retrying zero-length writes a limited number of times,
 * avoiding infinite loops.
 *
 * Based on a patch from https://github.com/leprechaun
 * https://github.com/pda/pheanstalk/pull/24
 *
 * A bitfield could be used instead of an array for efficiency.
 *
 * @author  Paul Annesley
 */
class WriteHistory
{
    private $limit;
    private $data = [];

    public function __construct(int $limit)
    {
        $this->limit = $limit;
    }

    /**
     * Whether the history has reached its limit of entries.
     */
    public function isFull(): bool
    {
        return count($this->data) >= $this->limit;
    }

    public function hasWrites(): bool
    {
        return (bool) array_sum($this->data);
    }

    public function isFullWithNoWrites(): bool
    {
        return $this->isFull() && !$this->hasWrites();
    }

    /**
     * Logs the return value from a write call.
     */
    public function log($write): void
    {
        if ($this->isFull()) {
            array_shift($this->data);
        }

        $this->data[] = (int) $write;
    }
}
