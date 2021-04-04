<?php

namespace CuyZ\WebZ\Tests\Unit\Http;

use CuyZ\WebZ\Core\Http\HttpClientFactory;
use CuyZ\WebZ\Core\Http\HttpClient;
use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Http\Payload\HttpPayload;
use CuyZ\WebZ\Http\Transformer\ScalarTransformer;
use CuyZ\WebZ\Tests\Fixture\Mocks;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \CuyZ\WebZ\Http\HttpTransport
 */
class HttpTransportTest extends TestCase
{
    public function test_returns_a_raw_result()
    {
        $payload = HttpPayload::request('GET', 'https://localhost')
            ->withTransformer(new ScalarTransformer());

        $client = HttpClient::mock(
            new Response(200, [], 'foo'),
            new Response(200, [], 'bar')
        );

        $transport = new HttpTransport(fn() => $client);

        $result = $transport->send($payload);

        /** @var RawResult $resultAsync */
        $resultAsync = $transport->sendAsync($payload, null)->wait();

        self::assertSame(['value' => 'foo'], $result->data());

        self::assertInstanceOf(RawResult::class, $resultAsync);
        self::assertSame(['value' => 'bar'], $resultAsync->data());
    }

    public function test_throws_an_exception_on_errors()
    {
        $this->expectException(RequestException::class);

        $payload = HttpPayload::request('GET', 'https://localhost');

        $client = HttpClient::mock(new RequestException('some_error', new Request('GET', 'test')));

        $transport = new HttpTransport(fn() => $client);
        $transport->send($payload);
    }
}
