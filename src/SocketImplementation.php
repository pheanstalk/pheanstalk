<?php
declare(strict_types=1);

namespace Pheanstalk;

enum SocketImplementation
{
    case STREAM;
    case SOCKET;
    case FSOCKOPEN;


}
