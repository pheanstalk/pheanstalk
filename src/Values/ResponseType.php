<?php

declare(strict_types=1);

namespace Pheanstalk\Values;

enum ResponseType: string
{
    // Global errors
    case OutOfMemory = 'OUT_OF_MEMORY';
    case InternalError = 'INTERNAL_ERROR';
    case Draining = 'DRAINING';
    case BadFormat = 'BAD_FORMAT';
    case UnknownCommand = 'UNKNOWN_COMMAND';

    
    case Inserted = 'INSERTED';
    case Buried = 'BURIED';
    case ExpectedCrlf = 'EXPECTED_CRLF';

    case JobTooBig = 'JOB_TOO_BIG';
    case Using = 'USING';
    case DeadlineSoon = 'DEADLINE_SOON';
    case Reserved = 'RESERVED';
    case Deleted = 'DELETED';
    case NotFound = 'NOT_FOUND';
    case Released = 'RELEASED';
    case Watching = 'WATCHING';
    case NotIgnored = 'NOT_IGNORED';
    case Found = 'FOUND';
    case Kicked = 'KICKED';
    case Ok = 'OK';
    case TimedOut = 'TIMED_OUT';
    case Touched = 'TOUCHED';
    case Paused = 'PAUSED';


    /**
     * @x-codeCoverageIgnore this is configuration and there is no useful way to cover it
     */
    public function hasData(): bool
    {
        return match ($this) {
            self::Reserved, self::Found, self::Ok => true,
            default => false
        };
    }
}
