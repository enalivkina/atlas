<?php

namespace Atlas\Console\Enum;

enum ConsoleEvent: string
{
    case INPUT_BEFORE_PARSE = 'input-before-parse';
    case INPUT_AFTER_PARSE = 'input-after-parse';
    case INPUT_AFTER_VALIDATE = 'input-after-validate';
}
