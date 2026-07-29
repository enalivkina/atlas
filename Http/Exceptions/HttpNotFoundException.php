<?php

declare(strict_types=1);

namespace Atlas\Http\Exceptions;

use Atlas\Http\Enum\StatusCode;

final class HttpNotFoundException extends HttpException
{
    public function __construct(?string $message = null)
    {
        parent::__construct(
            StatusCode::STATUS_NOT_FOUND->value,
            $message ?? StatusCode::STATUS_NOT_FOUND->reasonPhrase(),
        );
    }
}
