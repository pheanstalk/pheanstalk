<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration;

use PHPUnit\Framework\Assert;

/**
 * Tests for reported/discovered issues & bugs which don't fall into
 * an existing category of tests.
 * Relies on a running beanstalkd server.
 *
 * @coversNothing
 */
trait BugfixConnectionTest
{
    /**
     * Issue: NativeSocket's read() doesn't work with jobs larger than 8192 bytes.
     *
     * @see http://github.com/pda/pheanstalk/issues/4
     *
     * PHP 5.2.10-2ubuntu6.4 reads nearly double that on the first fread().
     * This is probably due to a prior call to fgets() pre-filling the read buffer.
     */
    public function testIssue4ReadingOver8192Bytes(): void
    {
        $length = 8192 * 3;

        $pheanstalk = $this->getPheanstalk();
        $data = str_repeat('.', $length);
        $jobId = $pheanstalk->put($data);
        $job = $pheanstalk->peek($jobId);
        Assert::assertSame($data, $job->getData());
    }

    /**
     * Issue: NativeSocket's read() cannot read all the bytes we want at once.
     *
     * @see http://github.com/pda/pheanstalk/issues/issue/16
     *
     * @author SlNPacifist
     */
    public function testIssue4ReadingDifferentNumberOfBytes(): void
    {
        $pheanstalk = $this->getPheanstalk();
        $maxLength = 10000;
        $delta = str_repeat('a', 1000);
        // Let's repeat 20 times to make problem more obvious on Linux OS (it happens randomly)
        for ($i = 0; $i < 16; $i++) {
            for ($message = $delta; strlen($message) < $maxLength; $message .= $delta) {
                $jobId = $pheanstalk->put($message);
                $job = $pheanstalk->peek($jobId);
                $pheanstalk->delete($job);
                Assert::assertEquals($job->getData(), $message);
            }
        }
    }
}
