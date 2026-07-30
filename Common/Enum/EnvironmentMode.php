<?php

namespace Atlas\Common\Enum;

enum EnvironmentMode: string
{
    case DEVELOPMENT = 'development';

    case PRODUCTION = 'production';
}