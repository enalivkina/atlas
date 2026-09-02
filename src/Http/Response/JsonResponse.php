<?php

declare(strict_types=1);

namespace Atlas\Http\Response;

use Atlas\Http\Response;
use Atlas\Http\Stream;

class JsonResponse extends Response
{
    public function __construct(mixed $data = null, int $status = 200)
    {
        $content = $data !== null ? json_encode($data, JSON_UNESCAPED_UNICODE) : '';

        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write($content);

        $headers['Content-Type'] = ['application/json'];

        parent::__construct(
            body: $stream,
            headers: $headers,
            statusCode: $status,
        );
    }
}
