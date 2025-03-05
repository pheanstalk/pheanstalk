<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration\Examples;

use Pheanstalk\PheanstalkPublisher;
use Pheanstalk\Values\TubeName;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class PublisherTest extends TestCase
{
    #[DoesNotPerformAssertions]
    public function testPublishJob(): void
    {
        if (SERVER_HOST === '') {
            self::markTestSkipped('No SERVER_HOST configured');
        }
        $pheanstalk = PheanstalkPublisher::create(SERVER_HOST);

        // Queue a Job
        $pheanstalk->useTube(new TubeName('tube1'));

        $pheanstalk->put("job payload goes here\n");
    }
}
