<?php

namespace Atlas\tests\Unit\Http;

use Atlas\Http\Response;
use Atlas\Http\Stream;
use Codeception\PHPUnit\TestCase;

final class ResponseTest extends TestCase
{
    private function createResponse(
        string $body = 'Hello',
        int $status = 200,
        string $reasonPhrase = '',
        array $headers = [],
    ): Response {
        $stream = new Stream(fopen('php://memory', 'rb+'));
        $stream->write($body);
        $stream->rewind();

        return new Response(
            body: $stream,
            headers: $headers,
            statusCode: $status,
            reasonPhrase: $reasonPhrase,
        );
    }

    public function testCreatesResponse(): void
    {
        $response = $this->createResponse();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('OK', $response->getReasonPhrase());
    }

    public function testUsesCustomReasonPhrase(): void
    {
        $response = $this->createResponse(
            status: 200,
            reasonPhrase: 'Everything Fine',
        );

        self::assertSame('Everything Fine', $response->getReasonPhrase());
    }

    public function testReturnsReasonPhraseFromStatusCode(): void
    {
        $response = $this->createResponse(
            status: 404,
        );

        self::assertSame('Not Found', $response->getReasonPhrase());
    }

    public function testWithStatusReturnsNewInstance(): void
    {
        $response = $this->createResponse();

        $newResponse = $response->withStatus(404);

        self::assertNotSame($response, $newResponse);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(404, $newResponse->getStatusCode());

        self::assertSame('OK', $response->getReasonPhrase());
        self::assertSame('Not Found', $newResponse->getReasonPhrase());
    }

    public function testWithStatusUsesCustomReasonPhrase(): void
    {
        $response = $this->createResponse();

        $newResponse = $response->withStatus(
            418,
            'I am a teapot :)',
        );

        self::assertSame(418, $newResponse->getStatusCode());
        self::assertSame(
            'I am a teapot :)',
            $newResponse->getReasonPhrase(),
        );
    }

    public function testResponseIsImmutable(): void
    {
        $response = $this->createResponse();

        $newResponse = $response->withStatus(500);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('OK', $response->getReasonPhrase());

        self::assertSame(500, $newResponse->getStatusCode());
        self::assertSame(
            'Internal Server Error',
            $newResponse->getReasonPhrase(),
        );
    }

    public function testKeepsProtocolVersion(): void
    {
        $stream = new Stream(fopen('php://memory', 'rb+'));

        $response = new Response(
            body: $stream,
            protocolVersion: '2.0',
        );

        self::assertSame(
            '2.0',
            $response->getProtocolVersion(),
        );
    }

    public function testStoresHeaders(): void
    {
        $response = $this->createResponse(
            headers: [
                'Content-Type' => ['application/json'],
                'Cache-Control' => ['no-cache'],
            ],
        );

        self::assertTrue(
            $response->hasHeader('Content-Type'),
        );

        self::assertSame(
            ['application/json'],
            $response->getHeader('Content-Type'),
        );

        self::assertSame(
            'application/json',
            $response->getHeaderLine('Content-Type'),
        );
    }

    public function testStoresBody(): void
    {
        $response = $this->createResponse('Atlas');

        $body = $response->getBody();

        $body->rewind();

        self::assertSame(
            'Atlas',
            $body->getContents(),
        );
    }
}
