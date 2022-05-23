<?php

declare(strict_types=1);

namespace Pheanstalk\Values;

/**
 * Simple value object that has a very basic render function
 */
final class TubeCommandTemplate
{
    private const PLACEHOLDER = '{tube}';

    public function __construct(private readonly string $value)
    {
        if (!str_contains($value, self::PLACEHOLDER)) {
            throw new \InvalidArgumentException("Tube command template must contain tube name placeholder");
        }
    }

    public function render(TubeName $tubeName): string
    {
        return strtr($this->value, [self::PLACEHOLDER => $tubeName]);
    }
}
