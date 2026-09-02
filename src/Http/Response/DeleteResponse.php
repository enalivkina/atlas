<?php

declare(strict_types=1);

namespace Atlas\Http\Response;

final class DeleteResponse extends JsonResponse
{
    public function __construct()
    {
        parent::__construct(null, 204);
    }
}
