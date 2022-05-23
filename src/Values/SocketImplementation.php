<?php

declare(strict_types=1);

namespace Pheanstalk\Values;

enum SocketImplementation
{
    case STREAM;
    case SOCKET;
    case FSOCKOPEN;
}
