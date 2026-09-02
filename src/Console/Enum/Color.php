<?php

namespace Atlas\Console\Enum;

enum Color: int
{
    case FG_RED = 31;
    case BG_RED = 41;
    case FG_GREEN = 32;
    case FG_BLUE = 34;
    case FG_LIGHT_GREEN = 92;
    case FG_LIGHT_YELLOW = 93;
    case FG_WHITE = 97;
}
