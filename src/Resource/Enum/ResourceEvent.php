<?php

namespace Atlas\Resource\Enum;

enum ResourceEvent: string
{
    case BEFORE_CREATE = 'before-create';
    case CREATED = 'created';
    case BEFORE_UPDATE = 'before-update';
    case UPDATED = 'updated';
    case BEFORE_DELETE = 'before-delete';
    case DELETED = 'deleted';
    case LIST_REQUEST = 'list-request';
    case VIEW_REQUEST = 'view-request';
}
