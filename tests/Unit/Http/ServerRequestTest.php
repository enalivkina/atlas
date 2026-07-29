<?php

namespace Atlas\tests\Unit\Http;

use Atlas\Http\ServerRequest;
use Atlas\Http\Stream;
use Atlas\Http\Uri;
use Codeception\PHPUnit\TestCase;

final class ServerRequestTest extends TestCase
{
    private function createRequest(
        string $uri = '/users',
        string $method = 'GET',
    ): ServerRequest {
        return new ServerRequest(
            uri: new Uri($uri),
            body: new Stream(fopen('php://memory', 'rb')),
            method: $method,
        );
    }

    public function testCreatesRequest(): void
    {
        $request = $this->createRequest();

        self::assertSame('GET', $request->getMethod());
        self::assertSame('/users', $request->getUri()->getPath());
    }

    public function testReturnsRequestTarget(): void
    {
        $request = $this->createRequest('/users');

        self::assertSame('/users', $request->getRequestTarget());
    }

    public function testReturnsRequestTargetWithQueryString(): void
    {
        $request = $this->createRequest('/users?page=2&sort=name');

        self::assertSame(
            '/users?page=2&sort=name',
            $request->getRequestTarget(),
        );
    }

    public function testReturnsSlashWhenPathIsEmpty(): void
    {
        $request = $this->createRequest('');

        self::assertSame('/', $request->getRequestTarget());
    }

    public function testWithMethodReturnsNewInstance(): void
    {
        $request = $this->createRequest();

        $newRequest = $request->withMethod('POST');

        self::assertNotSame($request, $newRequest);

        self::assertSame('GET', $request->getMethod());
        self::assertSame('POST', $newRequest->getMethod());
    }

    public function testWithUriReturnsNewInstance(): void
    {
        $request = $this->createRequest('/users');

        $newRequest = $request->withUri(new Uri('/posts'));

        self::assertNotSame($request, $newRequest);

        self::assertSame('/users', $request->getUri()->getPath());
        self::assertSame('/posts', $newRequest->getUri()->getPath());
    }

    public function testWithQueryParams(): void
    {
        $request = $this->createRequest();

        $newRequest = $request->withQueryParams([
            'page' => 2,
            'limit' => 10,
        ]);

        self::assertSame([], $request->getQueryParams());

        self::assertSame(
            [
                'page' => 2,
                'limit' => 10,
            ],
            $newRequest->getQueryParams(),
        );
    }

    public function testWithCookieParams(): void
    {
        $request = $this->createRequest();

        $newRequest = $request->withCookieParams([
            'PHPSESSID' => 'abc123',
        ]);

        self::assertSame([], $request->getCookieParams());

        self::assertSame(
            [
                'PHPSESSID' => 'abc123',
            ],
            $newRequest->getCookieParams(),
        );
    }

    public function testWithParsedBody(): void
    {
        $request = $this->createRequest();

        $newRequest = $request->withParsedBody([
            'name' => 'John',
            'age' => 30,
        ]);

        self::assertNull($request->getParsedBody());

        self::assertSame(
            [
                'name' => 'John',
                'age' => 30,
            ],
            $newRequest->getParsedBody(),
        );
    }

    public function testWithUploadedFiles(): void
    {
        $request = $this->createRequest();

        $files = [
            'avatar' => [
                'name' => 'photo.jpg',
            ],
        ];

        $newRequest = $request->withUploadedFiles($files);

        self::assertSame([], $request->getUploadedFiles());

        self::assertSame(
            $files,
            $newRequest->getUploadedFiles(),
        );
    }

    public function testWithAttribute(): void
    {
        $request = $this->createRequest();

        $newRequest = $request->withAttribute('userId', 10);

        self::assertSame([], $request->getAttributes());

        self::assertSame(
            [
                'userId' => 10,
            ],
            $newRequest->getAttributes(),
        );

        self::assertSame(
            10,
            $newRequest->getAttribute('userId'),
        );
    }

    public function testReturnsDefaultAttributeValue(): void
    {
        $request = $this->createRequest();

        self::assertSame(
            'guest',
            $request->getAttribute('role', 'guest'),
        );
    }

    public function testWithoutAttribute(): void
    {
        $request = $this->createRequest()
            ->withAttribute('userId', 15);

        $newRequest = $request->withoutAttribute('userId');

        self::assertSame(
            15,
            $request->getAttribute('userId'),
        );

        self::assertNull(
            $newRequest->getAttribute('userId'),
        );
    }

    public function testRequestIsImmutable(): void
    {
        $request = $this->createRequest();

        $newRequest = $request
            ->withQueryParams(['page' => 1])
            ->withCookieParams(['token' => '123'])
            ->withAttribute('id', 5);

        self::assertSame([], $request->getQueryParams());
        self::assertSame([], $request->getCookieParams());
        self::assertSame([], $request->getAttributes());

        self::assertSame(['page' => 1], $newRequest->getQueryParams());
        self::assertSame(['token' => '123'], $newRequest->getCookieParams());
        self::assertSame(['id' => 5], $newRequest->getAttributes());
    }

    public function testHostHeaderIsTakenFromUri(): void
    {
        $request = new ServerRequest(
            uri: new Uri('https://example.com:8443/users'),
            body: new Stream(fopen('php://memory', 'rb')),
        );

        self::assertSame(
            ['example.com:8443'],
            $request->getHeader('Host'),
        );
    }

    public function testPreserveHostKeepsExistingHostHeader(): void
    {
        $request = new ServerRequest(
            uri: new Uri('https://example.com'),
            body: new Stream(fopen('php://memory', 'rb')),
        );

        $newRequest = $request->withUri(
            new Uri('https://google.com'),
            true,
        );

        self::assertSame(
            ['example.com'],
            $newRequest->getHeader('Host'),
        );
    }

    public function testReplacingUriUpdatesHostHeader(): void
    {
        $request = new ServerRequest(
            uri: new Uri('https://example.com'),
            body: new Stream(fopen('php://memory', 'rb')),
        );

        $newRequest = $request->withUri(
            new Uri('https://google.com'),
        );

        self::assertSame(
            ['google.com'],
            $newRequest->getHeader('Host'),
        );
    }
}
