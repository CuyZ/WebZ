<?php

namespace CuyZ\WebZ\Tests\Unit\Http\Payload;

use CuyZ\WebZ\Http\Exception\MissingConfigException;
use CuyZ\WebZ\Http\Payload\HttpPayload;
use CuyZ\WebZ\Http\Transformer\JsonTransformer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CuyZ\WebZ\Http\Payload\HttpPayload
 * @uses   \CuyZ\WebZ\Http\Exception\MissingConfigException
 */
class HttpPayloadTest extends TestCase
{
    public function test_creation()
    {
        $payload = HttpPayload::request('foo', 'bar');

        self::assertInstanceOf(HttpPayload::class, $payload);
        self::assertSame('foo', $payload->method());
        self::assertSame('bar', $payload->url());
        self::assertIsArray($payload->options());
        self::assertEmpty($payload->options());
        self::assertNull($payload->transformer());
    }

    public function test_empty_method()
    {
        $this->expectException(MissingConfigException::class);
        $this->expectExceptionMessage('The option "method" is missing');

        HttpPayload::request()->method();
    }

    public function test_empty_url()
    {
        $this->expectException(MissingConfigException::class);
        $this->expectExceptionMessage('The option "url" is missing');

        HttpPayload::request()->url();
    }

    public function test_set_transformer_instance()
    {
        $payload = HttpPayload::request('foo', 'bar');

        $transformer = new JsonTransformer();

        $payload->withTransformer($transformer);

        self::assertSame($transformer, $payload->transformer());
    }

    public function test_returns_empty_url_string_if_base_uri_is_set()
    {
        $payload = new HttpPayload();

        $payload->withBaseUri('foo');

        self::assertSame('', $payload->url());
    }

    public function test_set_method()
    {
        $payload = new HttpPayload('foo');

        self::assertSame('foo', $payload->method());

        $payload->withMethod('bar');

        self::assertSame('bar', $payload->method());
    }

    public function test_set_url()
    {
        $payload = new HttpPayload(null, 'foo');

        self::assertSame('foo', $payload->url());

        $payload->withUrl('bar');

        self::assertSame('bar', $payload->url());
    }

    public function test_set_base_uri_option()
    {
        $payload = new HttpPayload();

        $payload->withBaseUri('foo');

        self::assertSame(['base_uri' => 'foo'], $payload->options());
    }

    public function test_set_body_option()
    {
        $payload = new HttpPayload();

        $payload->withBody('fiz');

        self::assertSame(['body' => 'fiz'], $payload->options());
    }

    public function test_set_json_option()
    {
        $payload = new HttpPayload();

        $payload->withJson(['a' => 'b']);

        self::assertSame(['json' => ['a' => 'b']], $payload->options());
    }

    public function test_set_query_params()
    {
        $payload = new HttpPayload();

        $payload->withQuery('foo', 'a');
        $payload->withQuery('bar', 'b');

        self::assertSame(
            [
                'query' => [
                    'foo' => 'a',
                    'bar' => 'b',
                ],
            ],
            $payload->options()
        );
    }

    public function test_set_headers()
    {
        $payload = new HttpPayload();

        $payload->withHeader('Foo', 'a');

        self::assertSame(
            [
                'headers' => [
                    'Foo' => ['a'],
                ],
            ],
            $payload->options()
        );

        $payload->withHeader('Foo', 'b');

        self::assertSame(
            [
                'headers' => [
                    'Foo' => ['a', 'b'],
                ],
            ],
            $payload->options()
        );

        $payload->withHeader('Bar', 'a');

        self::assertSame(
            [
                'headers' => [
                    'Foo' => ['a', 'b'],
                    'Bar' => ['a'],
                ],
            ],
            $payload->options()
        );

        $payload->withHeader('Bar', 'a');

        self::assertSame(
            [
                'headers' => [
                    'Foo' => ['a', 'b'],
                    'Bar' => ['a', 'a'],
                ],
            ],
            $payload->options()
        );
    }

    public function test_set_basic_auth_without_password()
    {
        $payload = new HttpPayload();

        $payload->withAuthBasic('fiz');

        self::assertSame(
            [
                'auth' => ['fiz'],
            ],
            $payload->options()
        );
    }

    public function test_set_basic_auth_with_password()
    {
        $payload = new HttpPayload();

        $payload->withAuthBasic('fiz', 'baz');

        self::assertSame(
            [
                'auth' => ['fiz', 'baz'],
            ],
            $payload->options()
        );
    }

    public function test_set_auth_bearer()
    {
        $payload = new HttpPayload();

        $payload->withAuthBearer('foo');

        self::assertSame(
            [
                'headers' => [
                    'Authorization' => ['Bearer foo'],
                ],
            ],
            $payload->options()
        );
    }
}
