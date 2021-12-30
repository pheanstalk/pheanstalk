<?php

declare(strict_types=1);

namespace Pheanstalk;

enum JobState: string
{

    case DELAYED = "delayed";
    case READY = "ready";
    case BURIED = "buried";
    case RESERVED = "reserved";
}
