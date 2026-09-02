<?php

declare(strict_types=1);

namespace Atlas\Logger\Enum;

enum LogContextEvent: string
{
    case ATTACH_CONTEXT = 'log.attach.context';
    case DETACH_CONTEXT = 'log.detach.context';
    case FLUSH_CONTEXT = 'log.flush.context';
    case ATTACH_EXTRAS = 'log.attach.extras';
    case FLUSH_EXTRAS = 'log.flush.extras';
    case ATTACH_CATEGORY = 'log.category.attach';
    case FLUSH_CATEGORY = 'log.category.flush';
}
