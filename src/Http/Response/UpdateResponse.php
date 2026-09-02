<?php

declare(strict_types=1);

namespace Atlas\Http\Response;

final class UpdateResponse extends JsonResponse
{
    public function __construct()
    {
        parent::__construct(null, 204);
    }
}
