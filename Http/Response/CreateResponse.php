<?php

declare(strict_types=1);

namespace Atlas\Http\Response;

final class CreateResponse extends JsonResponse
{
    public function __construct(?string $body = null)
    {
        parent::__construct($body, 201);
    }
}
