<?php

declare(strict_types=1);

namespace Pheanstalk\Contract;

use Pheanstalk\Values\Job;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\JobStats;
use Pheanstalk\Values\RawResponse;
use Pheanstalk\Values\ServerStats;
use Pheanstalk\Values\Success;
use Pheanstalk\Values\TubeList;
use Pheanstalk\Values\TubeName;
use Pheanstalk\Values\TubeStats;

/**
 * A command to be sent to the beanstalkd server, and response processing logic.
 * @internal
 */
interface CommandInterface
{
    /**
     * The command line, without trailing CRLF.
     */
    public function getCommandLine(): string;

    /**
     * Interprets a raw response object
     * MUST throw an exception if the parser determines the response to be invalid
     * MUST return the response type as defined in the PheanstalkInterface for the command type.
     * If the PheanstalkInterface has return type void, the implementation MUST return EmptySuccessResponse
     * @param RawResponse $response
     */
    public function interpret(RawResponse $response): int|Job|JobId|TubeName|TubeList|Success|JobStats|TubeStats|ServerStats;
}
