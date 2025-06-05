<?php

declare(strict_types=1);

namespace Pheanstalk\Exception;

use Pheanstalk\Values\TubeName;

final class TubeNotFoundException extends ClientException
{
    private const string UNKNOWN_TUBE = '[unknown]';

    public readonly string $tube;

    public function __construct(string|TubeName $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        if ($message instanceof TubeName) {
            $this->tube = (string) $message;
            $message = '';
        } else {
            $this->tube = self::UNKNOWN_TUBE;
        }

        parent::__construct(
            '' === $message ? sprintf('Tube "%s" not found.', $this->tube) : $message,
            $code,
            $previous,
        );
    }
}
