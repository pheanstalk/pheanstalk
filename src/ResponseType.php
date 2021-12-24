<?php
declare(strict_types=1);

namespace Pheanstalk;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Exception\ExpectedCrlfException;
use Pheanstalk\Exception\JobNotFoundException;
use Pheanstalk\Exception\JobTooBigException;
use Pheanstalk\Exception\NotIgnoredException;
use Pheanstalk\Exception\ServerBadFormatException;
use Pheanstalk\Exception\ServerDrainingException;
use Pheanstalk\Exception\ServerInternalErrorException;
use Pheanstalk\Exception\ServerOutOfMemoryException;
use Pheanstalk\Exception\ServerUnknownCommandException;

enum ResponseType: string
{
    // Global errors
    case OUT_OF_MEMORY = 'OUT_OF_MEMORY';
    case INTERNAL_ERROR = 'INTERNAL_ERROR';
    case DRAINING = 'DRAINING';
    case BAD_FORMAT = 'BAD_FORMAT';
    case UNKNOWN_COMMAND = 'UNKNOWN_COMMAND';

    
    case INSERTED = 'INSERTED';
    case BURIED = 'BURIED';
    case EXPECTED_CRLF = 'EXPECTED_CRLF';

    case JOB_TOO_BIG = 'JOB_TOO_BIG';
    case USING = 'USING';
    case DEADLINE_SOON = 'DEADLINE_SOON';
    case RESERVED = 'RESERVED';
    case DELETED = 'DELETED';
    case NOT_FOUND = 'NOT_FOUND';
    case RELEASED = 'RELEASED';
    case WATCHING = 'WATCHING';
    case NOT_IGNORED = 'NOT_IGNORED';
    case FOUND = 'FOUND';
    case KICKED = 'KICKED';
    case OK = 'OK';
    case TIMED_OUT = 'TIMED_OUT';
    case TOUCHED = 'TOUCHED';
    case PAUSED = 'PAUSED';


    public function argumentCount(): int
    {
        return match($this) {
            self::RESERVED, self::FOUND=> 2,
            self::OK, self::USING, self::WATCHING, self::INSERTED => 1,
            default => 0,
        };
    }

    public function hasData(): bool
    {
        return match($this) {
            self::RESERVED, self::FOUND, self::OK => true,
            default => false
        };
    }


}
