<?php

namespace CuyZ\WebZ\Tests\Unit\Http\Formatter;

use CuyZ\WebZ\Http\Formatter\HttpMessageFormatter;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @covers \CuyZ\WebZ\Http\Formatter\HttpMessageFormatter
 */
class HttpMessageFormatterTest extends TestCase
{
    private function steamFor(string $input): Stream
    {
        $stream = fopen('php://temp', 'r+');

        if ($input !== '') {
            fwrite($stream, $input);
            fseek($stream, 0);
        }

        return new Stream($stream);
    }

    public function requestsDataProvider()
    {
        $body = $this->steamFor('hello world');

        $request = (new Request('POST', 'https://localhost'))
            ->withRequestTarget('/hello?foo=bar')
            ->withHeader('X-Foo', 'bar')
            ->withHeader('User-Agent', 'cuyz/webz')
            ->withHeader('Content-Length', $body->getSize())
            ->withBody($body)
            ->withProtocolVersion('1.2');

        yield [
            'maxBodyLength' => null,
            'request' => $request,
            'output' => <<<REQUEST
POST /hello?foo=bar HTTP/1.2
Host: localhost
X-Foo: bar
User-Agent: cuyz/webz
Content-Length: 11

hello world
REQUEST,
        ];

        yield [
            'maxBodyLength' => 7,
            'request' => $request,
            'output' => <<<REQUEST
POST /hello?foo=bar HTTP/1.2
Host: localhost
X-Foo: bar
User-Agent: cuyz/webz
Content-Length: 11

hello w
REQUEST
        ];

        yield [
            'maxBodyLength' => 0,
            'request' => $request,
            'output' => <<<REQUEST
POST /hello?foo=bar HTTP/1.2
Host: localhost
X-Foo: bar
User-Agent: cuyz/webz
Content-Length: 11
REQUEST
        ];

        $body = $this->steamFor('foo');
        $body->detach();

        $request = (new Request('GET', 'https://localhost'))
            ->withBody($body);

        yield [
            'maxBodyLength' => null,
            'request' => $request,
            'output' => <<<REQUEST
GET / HTTP/1.1
Host: localhost
REQUEST
        ];

        $body = $this->steamFor("\x00");

        $request = (new Request('GET', 'https://localhost'))
            ->withBody($body);

        yield [
            'maxBodyLength' => null,
            'request' => $request,
            'output' => <<<REQUEST
GET / HTTP/1.1
Host: localhost

[binary stream omitted]
REQUEST
        ];
    }

    /**
     * @dataProvider requestsDataProvider
     * @param int|null $maxBodyLength
     * @param RequestInterface $request
     * @param string $expectedOutput
     */
    public function test_formats_a_request(?int $maxBodyLength, RequestInterface $request, string $expectedOutput)
    {
        $formatter = new HttpMessageFormatter($maxBodyLength);

        $output = $formatter->formatRequest($request);

        self::assertSame($expectedOutput, $output);
    }

    public function responsesDataProvider()
    {
        $body = $this->steamFor('hello world');

        $response = (new Response(404))
            ->withHeader('X-Foo', 'bar')
            ->withHeader('User-Agent', 'cuyz/webz')
            ->withHeader('Content-Length', $body->getSize())
            ->withBody($body)
            ->withProtocolVersion('1.2');

        yield [
            'maxBodyLength' => null,
            'response' => $response,
            'output' => <<<RESPONSE
HTTP/1.2 404 Not Found
X-Foo: bar
User-Agent: cuyz/webz
Content-Length: 11

hello world
RESPONSE,
        ];

        yield [
            'maxBodyLength' => 7,
            'response' => $response,
            'output' => <<<RESPONSE
HTTP/1.2 404 Not Found
X-Foo: bar
User-Agent: cuyz/webz
Content-Length: 11

hello w
RESPONSE
        ];

        yield [
            'maxBodyLength' => 0,
            'response' => $response,
            'output' => <<<RESPONSE
HTTP/1.2 404 Not Found
X-Foo: bar
User-Agent: cuyz/webz
Content-Length: 11
RESPONSE
        ];

        $body = $this->steamFor('foo');
        $body->detach();

        $response = (new Response(200))
            ->withBody($body);

        yield [
            'maxBodyLength' => null,
            'response' => $response,
            'output' => <<<RESPONSE
HTTP/1.1 200 OK
RESPONSE
        ];

        $body = $this->steamFor("\x00");

        $response = (new Response(200))
            ->withBody($body);

        yield [
            'maxBodyLength' => null,
            'response' => $response,
            'output' => <<<RESPONSE
HTTP/1.1 200 OK

[binary stream omitted]
RESPONSE
        ];
    }

    /**
     * @dataProvider responsesDataProvider
     * @param int|null $maxBodyLength
     * @param ResponseInterface $response
     * @param string $expectedOutput
     */
    public function test_formats_a_response(?int $maxBodyLength, ResponseInterface $response, string $expectedOutput)
    {
        $formatter = new HttpMessageFormatter($maxBodyLength);

        $output = $formatter->formatResponse($response);

        self::assertSame($expectedOutput, $output);
    }
}
