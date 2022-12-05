<?php

declare(strict_types=1);

namespace Pheanstalk\Tests\Integration\Examples;

use Pheanstalk\PheanstalkPublisher;
use Pheanstalk\Values\TubeName;
use PHPUnit\Framework\TestCase;

final class PublisherTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testPublishJob(): void
    {
        $pheanstalk = PheanstalkPublisher::create(SERVER_HOST);

        // Queue a Job
        $pheanstalk->useTube(new TubeName('tube1'));

        $pheanstalk->put("job payload goes here\n");
    }
}
