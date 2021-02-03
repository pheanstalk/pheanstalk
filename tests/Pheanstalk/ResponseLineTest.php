<?php
declare(strict_types=1);

namespace Pheanstalk\Tests;

use Pheanstalk\ResponseLine;

/**
 * @covers \Pheanstalk\ResponseLine
 */
class ResponseLineTest extends BaseTestCase
{
    public function testFactory()
    {
        $r = ResponseLine::fromString('   RESERVED 5 123  ');

    }
}