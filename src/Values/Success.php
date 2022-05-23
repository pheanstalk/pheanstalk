<?php

declare(strict_types=1);

namespace Pheanstalk\Values;

/**
 * This class indicates a successful operation with no (relevant) data
 * We don't use `null` for this because we cannot narrow return values to just null.
 * For example a command that either succeeds or throws an exception would look like this:
 * `public function interpret(RawResponse $response): null`, which is not valid.
 * Instead we use this class so that we get:
 * `public function interpret(RawResponse $response): Success`
 */
final class Success
{
}
