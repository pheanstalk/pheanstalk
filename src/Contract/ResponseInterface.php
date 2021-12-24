<?php

declare(strict_types=1);

namespace Pheanstalk\Contract;

use Pheanstalk\ResponseType;

/**
 * A response from the beanstalkd server.
 */
interface ResponseInterface
{
    // global error responses
    public const RESPONSE_OUT_OF_MEMORY = 'OUT_OF_MEMORY';
    public const RESPONSE_INTERNAL_ERROR = 'INTERNAL_ERROR';
    public const RESPONSE_DRAINING = 'DRAINING';
    public const RESPONSE_BAD_FORMAT = 'BAD_FORMAT';
    public const RESPONSE_UNKNOWN_COMMAND = 'UNKNOWN_COMMAND';

    // command responses
    public const RESPONSE_INSERTED = 'INSERTED';
    public const RESPONSE_BURIED = 'BURIED';
    public const RESPONSE_EXPECTED_CRLF = 'EXPECTED_CRLF';
    public const RESPONSE_JOB_TOO_BIG = 'JOB_TOO_BIG';
    public const RESPONSE_USING = 'USING';
    public const RESPONSE_DEADLINE_SOON = 'DEADLINE_SOON';
    public const RESPONSE_RESERVED = 'RESERVED';
    public const RESPONSE_DELETED = 'DELETED';
    public const RESPONSE_NOT_FOUND = 'NOT_FOUND';
    public const RESPONSE_RELEASED = 'RELEASED';
    public const RESPONSE_WATCHING = 'WATCHING';
    public const RESPONSE_NOT_IGNORED = 'NOT_IGNORED';
    public const RESPONSE_FOUND = 'FOUND';
    public const RESPONSE_KICKED = 'KICKED';
    public const RESPONSE_OK = 'OK';
    public const RESPONSE_TIMED_OUT = 'TIMED_OUT';
    public const RESPONSE_TOUCHED = 'TOUCHED';
    public const RESPONSE_PAUSED = 'PAUSED';

    public function getResponseType(): ResponseType;
}
