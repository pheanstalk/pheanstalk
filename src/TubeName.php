<?php
declare(strict_types=1);

namespace Pheanstalk;


use Stringable;

class TubeName implements Stringable
{
    const NAME_REGEX = '~^[A-Za-z0-9+/;.$_()][A-Za-z0-9\-+/;.$_()]*$~';
    /**
     * @param non-empty-string $tube
     */
    public function __construct(public readonly string $value)
    {
        if (!preg_match(self::NAME_REGEX, $value)) {
            throw new \InvalidArgumentException("Invalid tube name");
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }


}
