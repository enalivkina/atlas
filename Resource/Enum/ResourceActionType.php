<?php

namespace Atlas\Resource\Enum;

enum ResourceActionType: string
{
    case CREATE = 'create';
    case DELETE = 'delete';
    case UPDATE = 'update';
    case PATCH = 'patch';
    case VIEW = 'view';
    case INDEX = 'index';
}
