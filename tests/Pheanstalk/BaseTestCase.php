<?php

declare(strict_types=1);

namespace Pheanstalk\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class BaseTestCase
 * This is the base class for all tests, it is mainly here so that if we ever need to change the base class (like using
 * a phpunit polyfill) we'll only have to change 1 file
 * @package Pheanstalk
 */
abstract class BaseTestCase extends TestCase
{

}
