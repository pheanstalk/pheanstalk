<?php

declare(strict_types=1);

namespace Pheanstalk\Values;

enum CommandType: string
{
    case PUT = 'put';
    case USE = 'use';
    case RESERVE = 'reserve';
    case RESERVE_WITH_TIMEOUT = 'reserve-with-timeout';
    case RESERVE_JOB = 'reserve-job';
    case DELETE = 'delete';
    case RELEASE = 'release';
    case TOUCH = 'touch';
    case BURY = 'bury';
    case WATCH = 'watch';
    case IGNORE = 'ignore';
    case PEEK = 'peek';
    case PEEK_READY = 'peek-ready';
    case PEEK_BURIED = 'peek-buried';
    case PEEK_DELAYED = 'peek-delayed';
    case KICK = 'kick';
    case KICK_JOB = 'kick-job';
    case STATS_JOB = 'stats-job';
    case STATS = 'stats';
    case STATS_TUBE = 'stats-tube';
    case LIST_TUBES = 'list-tubes';
    case LIST_TUBE_USED = 'list-tube-used';
    case LIST_TUBES_WATCHED = 'list-tubes-watched';
    case PAUSE_TUBE = 'pause-tube';
}
