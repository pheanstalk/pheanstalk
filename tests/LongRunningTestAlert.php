<?php

declare(strict_types=1);

namespace Pheanstalk\Tests;

use PHPUnit\Runner\AfterTestHook;

/**
 * Source: https://www.aaronsaray.com/2021/finding-slow-tests-in-phpunit-9
 */
class LongRunningTestAlert implements AfterTestHook
{
    private const MAX_SECONDS_ALLOWED = 3;
    public function executeAfterTest(string $test, float $time): void
    {
        if ($time > self::MAX_SECONDS_ALLOWED) {
            fwrite(STDERR, sprintf("\nThe %s test took %s seconds!\n", $test, $time));
        }
    }
}
