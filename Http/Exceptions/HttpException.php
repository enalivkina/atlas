<?php

declare(strict_types=1);

namespace Atlas\Http\Exceptions;

class HttpException extends \Exception
{
    public function __construct(protected int $statusCode, string $message)
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
