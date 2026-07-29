<?php

declare(strict_types=1);

namespace Atlas\Http\Exceptions;

use Atlas\Http\Enum\StatusCode;

final class HttpBadRequestException extends HttpException
{
    public function __construct(mixed $message = null)
    {
        parent::__construct(
            StatusCode::STATUS_BAD_REQUEST->value,
            $message ?? StatusCode::STATUS_BAD_REQUEST->reasonPhrase(),
        );
    }
}
